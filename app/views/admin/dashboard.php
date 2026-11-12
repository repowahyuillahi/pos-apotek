<?php
/**
 * Dashboard Admin (Kepala Cabang).
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

$cabangId = $_SESSION['user_cabang_id'] ?? null;
$userNama = $_SESSION['user_nama'] ?? 'Admin';
$cabangNama = '';

if ($cabangId) {
    $c = DB::fetchOne("SELECT nama FROM cabang WHERE id = :id", ['id' => $cabangId]);
    $cabangNama = $c['nama'] ?? '';
}

$totalObat     = DB::fetchOne(
    "SELECT COUNT(*) as cnt FROM obat WHERE cabang_id = :cid AND is_active = 1",
    ['cid' => $cabangId]
)['cnt'] ?? 0;
$totalTransaksi = DB::fetchOne(
    "SELECT COUNT(*) as cnt FROM transaksi WHERE cabang_id = :cid AND DATE(tanggal) = CURDATE()",
    ['cid' => $cabangId]
)['cnt'] ?? 0;
$totalRevenue   = DB::fetchOne(
    "SELECT SUM(total) as sum FROM transaksi WHERE cabang_id = :cid AND DATE(tanggal) = CURDATE() AND is_closed = 1",
    ['cid' => $cabangId]
)['sum'] ?? 0;
$lowStockCount  = DB::fetchOne(
    "SELECT COUNT(*) as cnt FROM obat WHERE cabang_id = :cid AND stok > 0 AND stok <= stok_minimal",
    ['cid' => $cabangId]
)['cnt'] ?? 0;
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Dashboard</h1>
            <p class="text-muted" style="margin-bottom:0.5rem;">
                Cabang: <strong><?= esc($cabangNama) ?></strong>
            </p>
            <p class="text-muted" style="margin-bottom:2rem;">Halo, <?= esc($userNama) ?>.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Obat</div>
                    <div style="font-size:2rem; font-weight:700;"><?= esc($totalObat) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Transaksi Hari Ini</div>
                    <div style="font-size:2rem; font-weight:700;"><?= esc($totalTransaksi) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Pendapatan Hari Ini</div>
                    <div style="font-size:2rem; font-weight:700; color:var(--primary-color);"><?= format_rupiah((int)$totalRevenue) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Stok Menipis</div>
                    <div style="font-size:2rem; font-weight:700; color:var(--accent-warn);"><?= esc($lowStockCount) ?></div>
                </div>
            </div>

            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/admin?page=obat" class="btn btn-primary">Kelola Obat</a>
                <a href="<?= BASE_URL ?>/admin?page=transaksi" class="btn btn-secondary">Transaksi</a>
                <a href="<?= BASE_URL ?>/admin?page=laporan" class="btn btn-secondary">Laporan</a>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
