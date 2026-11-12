<?php
/**
 * Dashboard Owner / Superadmin.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

$cabangId = $_SESSION['user_cabang_id'] ?? null;

// Statistik
$totalUsers    = DB::fetchOne("SELECT COUNT(*) as cnt FROM user WHERE is_active = 1")['cnt'] ?? 0;
$totalObat     = DB::fetchOne("SELECT COUNT(*) as cnt FROM obat WHERE is_active = 1")['cnt'] ?? 0;
$totalTransaksi = DB::fetchOne(
    "SELECT COUNT(*) as cnt FROM transaksi WHERE DATE(tanggal) = CURDATE()"
)['cnt'] ?? 0;
$totalRevenue   = DB::fetchOne(
    "SELECT SUM(total) as sum FROM transaksi WHERE DATE(tanggal) = CURDATE() AND is_closed = 1"
)['sum'] ?? 0;
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Dashboard</h1>
            <p class="text-muted" style="margin-bottom:2rem;">Selamat datang, <?= esc($_SESSION['user_nama']) ?>.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total User</div>
                    <div style="font-size:2rem; font-weight:700;"><?= esc($totalUsers) ?></div>
                </div>
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
            </div>

            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/owner?page=cabang" class="btn btn-primary">Kelola Cabang</a>
                <a href="<?= BASE_URL ?>/owner?page=user" class="btn btn-secondary">Kelola User</a>
                <a href="<?= BASE_URL ?>/owner?page=brand" class="btn btn-secondary">Setting Brand</a>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
