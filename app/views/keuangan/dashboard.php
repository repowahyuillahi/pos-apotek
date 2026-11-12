<?php
/**
 * Dashboard Keuangan.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

// Total semua cabang
$allTransaksi = DB::fetchOne(
    "SELECT COUNT(*) as cnt FROM transaksi WHERE DATE(tanggal) = CURDATE() AND is_closed = 1"
)['cnt'] ?? 0;
$allRevenue   = DB::fetchOne(
    "SELECT SUM(total) as sum FROM transaksi WHERE DATE(tanggal) = CURDATE() AND is_closed = 1"
)['sum'] ?? 0;

// Breakdown per cabang
$cabangs = DB::query("SELECT id, nama FROM cabang WHERE is_active = 1");
$perCabang = [];
foreach ($cabangs as $cab) {
    $stats = DB::fetchOne(
        "SELECT COUNT(*) as cnt, SUM(total) as sum FROM transaksi
         WHERE cabang_id = :cid AND DATE(tanggal) = CURDATE() AND is_closed = 1",
        ['cid' => $cab['id']]
    );
    $perCabang[$cab['id']] = [
        'nama'   => $cab['nama'],
        'transaksi' => (int)($stats['cnt'] ?? 0),
        'revenue'   => (int)($stats['sum'] ?? 0),
    ];
}
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Dashboard Keuangan</h1>
            <p class="text-muted" style="margin-bottom:2rem;">Halo, <?= esc($_SESSION['user_nama']) ?>.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Transaksi Hari Ini</div>
                    <div style="font-size:2rem; font-weight:700;"><?= esc($allTransaksi) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Pendapatan Hari Ini</div>
                    <div style="font-size:2rem; font-weight:700; color:var(--primary-color);"><?= format_rupiah((int)$allRevenue) ?></div>
                </div>
            </div>

            <h2 style="font-size:1.25rem; font-weight:600; margin-bottom:1rem;">Per Cabang</h2>
            <div class="table-wrapper" style="max-width:700px;">
                <table>
                    <thead>
                        <tr>
                            <th>Cabang</th>
                            <th class="text-right">Transaksi</th>
                            <th class="text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perCabang as $id => $data): ?>
                            <tr>
                                <td><?= esc($data['nama']) ?></td>
                                <td class="text-right"><?= esc($data['transaksi']) ?></td>
                                <td class="text-right"><?= format_rupiah($data['revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($perCabang)): ?>
                            <tr>
                                <td colspan="3" class="text-center" style="color:var(--text-muted); padding:1rem;">
                                    Belum ada data.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/keuangan?page=laporan_penjualan" class="btn btn-primary">Laporan Penjualan</a>
                <a href="<?= BASE_URL ?>/keuangan?page=laporan_keu" class="btn btn-secondary">Laporan Keuangan</a>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
