<?php
/**
 * Laporan Penjualan — Keuangan.
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
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Laporan Penjualan</h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Data transaksi untuk analisis keuangan.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <form method="get" style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="page" value="laporan_penjualan">
                <input type="date" name="start" class="form-control" value="<?= esc($startDate) ?>" style="width:180px;">
                <span style="color:var(--text-muted);">s.d.</span>
                <input type="date" name="end" class="form-control" value="<?= esc($endDate) ?>" style="width:180px;">
                <button type="submit" class="btn btn-secondary btn-sm">Tampilkan</button>
                <?php if ($startDate !== date('Y-m-d') || $endDate !== date('Y-m-d')): ?>
                    <a href="<?= BASE_URL ?>/keuangan?page=laporan_penjualan" class="btn btn-secondary btn-sm">Hari Ini</a>
                <?php endif; ?>
            </form>

            <!-- Ringkasan -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Transaksi</div>
                    <div style="font-size:1.5rem; font-weight:700;"><?= esc($summary['total_transaksi']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Pendapatan</div>
                    <div style="font-size:1.5rem; font-weight:700; color:var(--primary-color);"><?= format_rupiah($summary['total_revenue']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Rata-rata</div>
                    <div style="font-size:1.25rem; font-weight:600;">
                        <?= $summary['total_transaksi'] > 0
                            ? format_rupiah((int)($summary['total_revenue'] / $summary['total_transaksi']))
                            : 'Rp 0' ?>
                    </div>
                </div>
            </div>

            <!-- Metode Bayar Breakdown -->
            <h2 style="font-size:1.125rem; font-weight:600; margin-bottom:1rem;">Metode Pembayaran</h2>
            <div class="table-wrapper" style="max-width:500px;">
                <table>
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = $summary['total_revenue'];
                        $methods = [
                            ['nama' => 'Tunai',    'val' => $summary['tunai']],
                            ['nama' => 'Kartu',    'val' => $summary['kartu']],
                            ['nama' => 'QRIS',     'val' => $summary['qris']],
                            ['nama' => 'Transfer', 'val' => $summary['transfer']],
                        ];
                        foreach ($methods as $m):
                            $pct = $total > 0 ? round(($m['val'] / $total) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= esc($m['nama']) ?></td>
                            <td class="text-right"><?= format_rupiah($m['val']) ?></td>
                            <td class="text-right"><?= esc($pct) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
