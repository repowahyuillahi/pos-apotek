<?php
/**
 * Cetak Struk — preview struk transaksi + trigger cetak.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/print_helper.php';
require_once __DIR__ . '/../models/TransaksiModel.php';

require_login();

$transaksiId = $_GET['id'] ?? null;
$transaksi = null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($transaksiId) {
    $transaksi = TransaksiModel::find((int)$transaksiId);
}

// Jika POST cetak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cetak') {
    if ($transaksi) {
        $printData = [
            'nama_apotek'   => $transaksi['no_transaksi'] . ' — ' . (SettingBrandModel::get()['nama_apotek'] ?? 'Apotek'),
            'no_transaksi'  => $transaksi['no_transaksi'],
            'tanggal'       => format_datetime($transaksi['tanggal']),
            'kasir'         => $transaksi['kasir_nama'] ?? 'Unknown',
            'items'         => $transaksi['details'],
            'total'         => (int)$transaksi['total'],
            'metode_bayar'  => $transaksi['metode_bayar'],
        ];
        printToLocal($printData);
        setFlash('success', 'Cetak berhasil dikirim ke printer.');
        header('Location: ' . BASE_URL . '/pos/cetak.php?id=' . $transaksiId);
        exit;
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

$brand = SettingBrandModel::get();
$brandName = $brand['nama_apotek'] ?? 'Apotek';
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<style>
    .struk-layout {
        max-width: 500px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    .struk-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .struk-header {
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px dashed var(--border-color);
    }
    .struk-brand-name {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    .struk-no {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }
    .struk-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--border-color);
    }
    .struk-items {
        margin-bottom: 1.5rem;
    }
    .struk-item-row {
        display: flex;
        justify-content: space-between;
        padding: 0.375rem 0;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f3f4;
    }
    .struk-item-row:last-child {
        border-bottom: none;
    }
    .struk-item-left {
        flex: 1;
    }
    .struk-item-qty {
        color: var(--text-muted);
        margin-right: 0.5rem;
    }
    .struk-item-right {
        text-align: right;
        font-weight: 500;
        min-width: 100px;
    }
    .struk-footer {
        border-top: 2px dashed var(--border-color);
        padding-top: 1rem;
    }
    .struk-total-row {
        display: flex;
        justify-content: space-between;
        padding: 0.375rem 0;
        font-size: 0.9rem;
    }
    .struk-total-final {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0 0.375rem;
        font-size: 1.25rem;
        font-weight: 700;
        border-top: 1px solid var(--border-color);
        margin-top: 0.375rem;
    }
    .struk-total-final .value {
        color: var(--primary-color);
    }
    .struk-thanks {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .struk-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        justify-content: center;
    }
</style>

<div class="struk-layout">
    <?php if ($flash): ?>
        <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1rem;">
            <?= esc($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$transaksi): ?>
        <div class="struk-card">
            <div class="text-center" style="padding:2rem; color:var(--text-muted);">
                Transaksi tidak ditemukan.
            </div>
        </div>
    <?php else: ?>
        <div class="struk-card">
            <div class="struk-header">
                <div class="struk-brand-name"><?= esc($brandName) ?></div>
                <div class="struk-no">No. Transaksi: <strong><?= esc($transaksi['no_transaksi']) ?></strong></div>
            </div>

            <div class="struk-info">
                <span>Tanggal: <?= esc(format_datetime($transaksi['tanggal'])) ?></span>
                <span>Kasir: <?= esc($transaksi['kasir_nama'] ?? 'Unknown') ?></span>
            </div>

            <div class="struk-items">
                <?php foreach ($transaksi['details'] as $item): ?>
                    <div class="struk-item-row">
                        <div class="struk-item-left">
                            <span class="struk-item-qty">x<?= esc($item['jumlah']) ?></span>
                            <?= esc($item['nama']) ?>
                        </div>
                        <div class="struk-item-right">
                            <?= format_rupiah((int)$item['subtotal']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="struk-footer">
                <?php foreach (['total' => 'Total'] as $label => $key): ?>
                    <div class="struk-total-row">
                        <span><?= $label ?></span>
                        <span><?= format_rupiah((int)$transaksi[$key]) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="struk-total-final">
                    <span>Grand Total</span>
                    <span class="value"><?= format_rupiah((int)$transaksi['total']) ?></span>
                </div>
            </div>

            <div class="struk-thanks">
                Terima kasih telah berbelanja di <?= esc($brandName) ?>!
            </div>
        </div>

        <div class="struk-actions">
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="cetak">
                <button type="submit" class="btn btn-primary">
                    🖨 Cetak ke Printer
                </button>
            </form>
            <a href="<?= BASE_URL ?>/pos" class="btn btn-secondary">
                Kembali ke POS
            </a>
        </div>
    <?php endif; ?>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
