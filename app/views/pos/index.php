<?php
/**
 * POS Interface — halaman Point of Sale.
 * Ditampilkan untuk role: kasir, admin, owner, superadmin.
 *
 * Fitur:
 *   - Daftar obat (searchable)
 *   - Keranjang (session-based)
 *   - Checkout
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../models/ObatModel.php';

$userNama = $_SESSION['user_nama'] ?? 'Kasir';
$cabangId = $_SESSION['user_cabang_id'] ?? null;
$obats = $obats ?? [];
$cart = $_SESSION['cart'] ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Hitung total
$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += ($item['harga'] ?? 0) * ($item['qty'] ?? 0);
}
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<style>
    .pos-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        min-height: calc(100vh - 60px);
    }
    .pos-obat-list {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        overflow-y: auto;
        max-height: calc(100vh - 100px);
    }
    .pos-search {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    .pos-search:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(26,115,232,0.15);
    }
    .pos-obat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .pos-obat-card {
        background: var(--bg-color);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem;
        cursor: pointer;
        transition: box-shadow 0.15s, border-color 0.15s;
        position: relative;
    }
    .pos-obat-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    .pos-obat-card.low-stock::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent-warn);
    }
    .pos-obat-nama {
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pos-obat-ket {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .pos-obat-harga {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-top: 0.25rem;
    }
    .pos-cart {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 100px);
    }
    .pos-cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 1rem;
    }
    .pos-cart-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .pos-cart-item:last-child {
        border-bottom: none;
    }
    .pos-qty-btn {
        width: 28px;
        height: 28px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: #fff;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s;
    }
    .pos-qty-btn:hover {
        background: var(--bg-color);
    }
    .pos-qty-value {
        min-width: 30px;
        text-align: center;
        font-weight: 500;
    }
    .pos-cart-footer {
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }
    .pos-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .pos-total-label {
        font-size: 1rem;
        font-weight: 500;
    }
    .pos-total-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    .pos-checkout-btn {
        width: 100%;
        padding: 1rem;
        font-size: 1.125rem;
        font-weight: 600;
        min-height: 52px;
    }
    .pos-empty-cart {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }
    .pos-no-obat {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }
    @media (max-width: 900px) {
        .pos-layout {
            grid-template-columns: 1fr;
        }
        .pos-cart {
            height: auto;
            max-height: 400px;
        }
    }
</style>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <!-- Sidebar disembunyikan di POS, kita pakai layout penuh -->
    <main class="main-content" style="flex:1; padding:1.5rem; background:var(--bg-color);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:700;">Point of Sale</h1>
                <p class="text-muted" style="margin-top:0.25rem;">Halo, <?= esc($userNama) ?></p>
            </div>
            <div style="display:flex; gap:0.75rem;">
                <a href="<?= BASE_URL ?>/admin" class="btn btn-secondary btn-sm">Dashboard</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="clearCart">
                    <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Kosongkan keranjang?');">Clear Cart</button>
                </form>
            </div>
        </div>

        <div class="pos-layout">
            <!-- Daftar Obat -->
            <div class="pos-obat-list">
                <input type="text" id="posSearch" class="pos-search" placeholder="Cari obat..." autocomplete="off">
                <div id="posObatGrid" class="pos-obat-grid">
                    <?php if (empty($obats)): ?>
                        <div class="pos-no-obat" style="grid-column:1/-1;">
                            Belum ada obat. Hubungi admin untuk menambahkan obat.
                        </div>
                    <?php else: ?>
                        <?php foreach ($obats as $ob): ?>
                            <?php $low = ($ob['stok'] > 0 && $ob['stok'] <= $ob['stok_minimal']); ?>
                            <div class="pos-obat-card <?= $low ? 'low-stock' : '' ?>"
                                 onclick="addToCart(<?= $ob['id'] ?>, 1)"
                                 title="Klik untuk tambah ke keranjang">
                                <div class="pos-obat-nama"><?= esc($ob['nama']) ?></div>
                                <div class="pos-obat-ket">Kode: <?= esc($ob['kode']) ?> | <?= esc($ob['satuan']) ?></div>
                                <div class="pos-obat-harga"><?= format_rupiah((int)$ob['harga_jual']) ?></div>
                                <div class="pos-obat-ket" style="margin-top:0.25rem;">Stok: <?= esc($ob['stok']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Keranjang -->
            <div class="pos-cart">
                <div class="pos-cart-header">
                    <h2 style="font-size:1.125rem; font-weight:600;">Keranjang</h2>
                    <span style="font-size:0.875rem; color:var(--text-muted);">
                        <?= count($cart) ?> item
                    </span>
                </div>

                <div class="pos-cart-items">
                    <?php if (empty($cart)): ?>
                        <div class="pos-empty-cart">
                            <div style="font-size:2rem; margin-bottom:0.5rem;">🛒</div>
                            <p>Keranjang kosong</p>
                            <p style="font-size:0.8rem; margin-top:0.25rem;">Klik obat untuk tambah.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart as $obatId => $item): ?>
                            <div class="pos-cart-item">
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:0.875rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= esc($item['nama']) ?>
                                    </div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.125rem;">
                                        <?= format_rupiah($item['harga']) ?>/<?= esc($item['satuan'] ?? 'pcs') ?>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.25rem;">
                                    <button class="pos-qty-btn" onclick="updateQty(<?= $obatId ?>, -1)">−</button>
                                    <span class="pos-qty-value"><?= esc($item['qty']) ?></span>
                                    <button class="pos-qty-btn" onclick="updateQty(<?= $obatId ?>, 1)">+</button>
                                </div>
                                <div style="width:70px; text-align:right; font-weight:500;">
                                    <?= format_rupiah(($item['harga'] ?? 0) * ($item['qty'] ?? 0)) ?>
                                </div>
                                <button class="pos-qty-btn" style="color:var(--accent-warn);" onclick="removeFromCart(<?= $obatId ?>)" title="Hapus">
                                    ×
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="pos-cart-footer">
                    <div class="pos-total-row">
                        <span class="pos-total-label">Total</span>
                        <span class="pos-total-value"><?= format_rupiah((int)$cartTotal) ?></span>
                    </div>
                    <form method="post">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary btn-lg pos-checkout-btn" <?= empty($cart) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                            Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function addToCart(obatId, qty) {
    fetch('<?= BASE_URL ?>/pos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=addToCart&obat_id=' + obatId + '&qty=' + qty
    }).then(() => location.reload());
}

function updateQty(obatId, delta) {
    fetch('<?= BASE_URL ?>/pos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=updateCart&obat_id=' + obatId + '&qty=' + delta
    }).then(() => location.reload());
}

function removeFromCart(obatId) {
    if (confirm('Hapus dari keranjang?')) {
        fetch('<?= BASE_URL ?>/pos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=removeFromCart&obat_id=' + obatId
        }).then(() => location.reload());
    }
}

// Live search
document.getElementById('posSearch').addEventListener('input', function(e) {
    var q = e.target.value.toLowerCase();
    var cards = document.querySelectorAll('.pos-obat-card');
    cards.forEach(function(card) {
        var nama = card.querySelector('.pos-obat-nama').textContent.toLowerCase();
        card.style.display = nama.includes(q) ? '' : 'none';
    });
});
</script>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
