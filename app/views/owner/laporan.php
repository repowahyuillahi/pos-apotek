<?php
/**
 * Laporan Owner / Superadmin.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

$flash     = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate   = $_GET['end']   ?? date('Y-m-d');
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Laporan Semua Cabang</h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Lihat performa semua cabang dalam periode terpilih.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <form method="get" style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="page" value="laporan">
                <input type="date" name="start" class="form-control" value="<?= esc($startDate) ?>" style="width:180px;">
                <span style="color:var(--text-muted);">s.d.</span>
                <input type="date" name="end" class="form-control" value="<?= esc($endDate) ?>" style="width:180px;">
                <button type="submit" class="btn btn-secondary btn-sm">Tampilkan</button>
                <?php if ($startDate !== date('Y-m-d') || $endDate !== date('Y-m-d')): ?>
                    <a href="<?= BASE_URL ?>/owner?page=laporan" class="btn btn-secondary btn-sm">Hari Ini</a>
                <?php endif; ?>
            </form>

            <!-- Ringkasan -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Transaksi</div>
                    <div style="font-size:1.75rem; font-weight:700;"><?= esc($summary['total_transaksi']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Pendapatan</div>
                    <div style="font-size:1.75rem; font-weight:700; color:var(--primary-color);"><?= format_rupiah($summary['total_revenue']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Tunai</div>
                    <div style="font-size:1.375rem; font-weight:600;"><?= format_rupiah($summary['tunai']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">QRIS</div>
                    <div style="font-size:1.375rem; font-weight:600;"><?= format_rupiah($summary['qris']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Transfer</div>
                    <div style="font-size:1.375rem; font-weight:600;"><?= format_rupiah($summary['transfer']) ?></div>
                </div>
            </div>

            <!-- Per Cabang -->
            <h2 style="font-size:1.125rem; font-weight:600; margin-bottom:1rem;">Breakdown Per Cabang</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Cabang</th>
                            <th class="text-right">Transaksi</th>
                            <th class="text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Hitung per cabang
                        $cabangs = DB::query("SELECT id, nama FROM cabang WHERE is_active = 1 ORDER BY id");
                        foreach ($cabangs as $cab): 
                            $stats = DB::fetchOne(
                                "SELECT COUNT(*) as cnt, SUM(total) as sum FROM transaksi
                                 WHERE cabang_id = :cid AND is_closed = 1
                                   AND DATE(tanggal) >= :start AND DATE(tanggal) <= :end",
                                ['cid' => $cab['id'], 'start' => $startDate, 'end' => $endDate]
                            );
                            $cnt   = (int)($stats['cnt'] ?? 0);
                            $sum   = (int)($stats['sum'] ?? 0);
                        ?>
                        <tr>
                            <td><strong><?= esc($cab['nama']) ?></strong></td>
                            <td class="text-right"><?= esc($cnt) ?></td>
                            <td class="text-right"><?= format_rupiah($sum) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
