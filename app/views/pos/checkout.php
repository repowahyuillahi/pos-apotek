<?php
/**
 * Checkout — konfirmasi transaksi & simpan.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../models/ObatModel.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
require_once __DIR__ . '/../models/SettingBrandModel.php';

require_login();
require_role(['kasir', 'admin', 'owner', 'superadmin']);

$cabangId = $_SESSION['user_cabang_id'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Hitung total
$cartTotal = 0;
$cartItems = [];
foreach ($cart as $obatId => $item) {
    $subtotal = ($item['harga'] ?? 0) * ($item['qty'] ?? 0);
    $cartTotal += $subtotal;
    $cartItems[] = [
        'obat_id'   => $obatId,
        'nama'      => $item['nama'],
        'kode'      => $item['kode'],
        'qty'       => $item['qty'],
        'harga'     => $item['harga'],
        'subtotal'  => $subtotal,
        'satuan'    => $item['satuan'],
    ];
}

// Jika POST checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'processCheckout') {
    $metode_bayar = $_POST['metode_bayar'] ?? 'tunai';
    $bayar        = (int)($_POST['bayar'] ?? 0);

    if (empty($cartItems)) {
        setFlash('danger', 'Keranjang kosong.');
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }

    // Validasi stok
    foreach ($cartItems as $item) {
        $obat = ObatModel::find($item['obat_id']);
        if (!$obat) {
            setFlash('danger', 'Obat tidak ditemukan: ' . $item['nama']);
            header('Location: ' . BASE_URL . '/pos');
            exit;
        }
        if (($obat['stok'] ?? 0) < $item['qty']) {
            setFlash('danger', 'Stok tidak cukup untuk ' . $item['nama'] . ' (tersedia: ' . $obat['stok'] . ', dipesan: ' . $item['qty'] . ').');
            header('Location: ' . BASE_URL . '/pos');
            exit;
        }
    }

    try {
        DB::connect()->beginTransaction();

        // Buat transaksi
        $noTransaksi = generate_no_transaksi($cabangId);
        $transaksiId = TransaksiModel::create([
            'cabang_id'    => $cabangId,
            'user_id'      => $_SESSION['user_id'],
            'no_transaksi' => $noTransaksi,
            'total'        => $cartTotal,
            'metode_bayar' => $metode_bayar,
        ]);

        // Simpan detail & kurangi stok
        foreach ($cartItems as $item) {
            TransaksiModel::addDetail($transaksiId, [
                'obat_id'    => $item['obat_id'],
                'jumlah'     => $item['qty'],
                'harga_satuan'=> $item['harga'],
                'subtotal'   => $item['subtotal'],
            ]);

            // Kurangi stok
            $obat = ObatModel::find($item['obat_id']);
            $newStok = max(0, ($obat['stok'] ?? 0) - $item['qty']);
            ObatModel::update($item['obat_id'], ['stok' => $newStok]);
        }

        DB::connect()->commit();

        // Kosongkan keranjang
        unset($_SESSION['cart']);

        setFlash('success', 'Transaksi #' . $noTransaksi . ' berhasil. Total: ' . format_rupiah($cartTotal) . '.');
        header('Location: ' . BASE_URL . '/pos/cetak.php?id=' . $transaksiId);
        exit;
    } catch (Exception $e) {
        DB::connect()->rollBack();
        setFlash('danger', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<style>
    .checkout-layout {
        max-width: 700px;
        margin: 0 auto;
        padding: 2rem;
    }
    .checkout-items {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .checkout-item-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }
    .checkout-item-row:last-child {
        border-bottom: none;
    }
    .checkout-total {
        display: flex;
        justify-content: space-between;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 2px solid var(--border-color);
        font-size: 1.25rem;
        font-weight: 700;
    }
    .checkout-total .value {
        color: var(--primary-color);
    }
    .checkout-form .form-group {
        margin-bottom: 1rem;
    }
    .checkout-metode {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .checkout-metode label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }
    .checkout-metode label:hover {
        border-color: var(--primary-color);
    }
    .checkout-metode input[type="radio"]:checked + span {
        color: var(--primary-color);
        font-weight: 600;
    }
    .checkout-metode input[type="radio"]:checked ~ span {
    }
    .checkout-metode label:has(input:checked) {
        border-color: var(--primary-color);
        background: rgba(26,115,232,0.05);
    }
    .checkout-bayar-input {
        max-width: 200px;
    }
</style>

<div class="checkout-layout">
    <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Checkout</h1>
    <p class="text-muted" style="margin-bottom:1.5rem;">Konfirmasi transaksi.</p>

    <?php if ($flash): ?>
        <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
            <?= esc($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="checkout-items">
        <?php foreach ($cartItems as $item): ?>
            <div class="checkout-item-row">
                <div>
                    <strong><?= esc($item['nama']) ?></strong>
                    <span style="color:var(--text-muted);"> x<?= esc($item['qty']) ?></span>
                </div>
                <div style="font-weight:500;"><?= format_rupiah($item['subtotal']) ?></div>
            </div>
        <?php endforeach; ?>
        <div class="checkout-total">
            <span>Total</span>
            <span class="value"><?= format_rupiah((int)$cartTotal) ?></span>
        </div>
    </div>

    <form method="post" class="checkout-form">
        <input type="hidden" name="action" value="processCheckout">

        <div class="form-group">
            <label class="form-label">Metode Bayar</label>
            <div class="checkout-metode">
                <label>
                    <input type="radio" name="metode_bayar" value="tunai" <?= ($metode_bayar ?? 'tunai') === 'tunai' ? 'checked' : '' ?>>
                    <span>Tunai</span>
                </label>
                <label>
                    <input type="radio" name="metode_bayar" value="kartu" <?= ($metode_bayar ?? '') === 'kartu' ? 'checked' : '' ?>>
                    <span>Kartu</span>
                </label>
                <label>
                    <input type="radio" name="metode_bayar" value="qris" <?= ($metode_bayar ?? '') === 'qris' ? 'checked' : '' ?>>
                    <span>QRIS</span>
                </label>
                <label>
                    <input type="radio" name="metode_bayar" value="transfer" <?= ($metode_bayar ?? '') === 'transfer' ? 'checked' : '' ?>>
                    <span>Transfer</span>
                </label>
            </div>
        </div>

        <?php if (($metode_bayar ?? 'tunai') === 'tunai'): ?>
            <div class="form-group">
                <label class="form-label" for="bayar">Jumlah Bayar</label>
                <input type="number" id="bayar" name="bayar" min="<?= $cartTotal ?>" step="1"
                    class="form-control checkout-bayar-input"
                    value="<?= $cartTotal ?>"
                    required
                    autofocus
                >
                <div class="form-hint">Minimal: <?= format_rupiah($cartTotal) ?></div>
            </div>
        <?php endif; ?>

        <div style="display:flex; gap:1rem; margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
                Proses Transaksi
            </button>
            <a href="<?= BASE_URL ?>/pos" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </form>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
