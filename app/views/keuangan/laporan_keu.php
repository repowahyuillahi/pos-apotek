<?php
/**
 * Laporan Keuangan — ringkasan keuangan untuk keuangan role.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

$flash     = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate   = $_GET['end']   ?? date('Y-m-d');

// Ambil data keuangan
$summary = TransaksiModel::getSummary(null, $startDate, $endDate);
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Laporan Keuangan</h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Ringkasan keuangan untuk periode <?= esc($startDate) ?> s.d. <?= esc($endDate) ?>.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <form method="get" style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="page" value="laporan_keu">
                <input type="date" name="start" class="form-control" value="<?= esc($startDate) ?>" style="width:180px;">
                <span style="color:var(--text-muted);">s.d.</span>
                <input type="date" name="end" class="form-control" value="<?= esc($endDate) ?>" style="width:180px;">
                <button type="submit" class="btn btn-secondary btn-sm">Tampilkan</button>
            </form>

            <!-- Ringkasan Besar -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Pendapatan Total</div>
                    <div style="font-size:2rem; font-weight:700; color:var(--primary-color);"><?= format_rupiah($summary['total_revenue']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Total Transaksi</div>
                    <div style="font-size:2rem; font-weight:700;"><?= esc($summary['total_transaksi']) ?></div>
                </div>
                <div class="card">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Rata-rata/Transaksi</div>
                    <div style="font-size:1.5rem; font-weight:600;">
                        <?= $summary['total_transaksi'] > 0
                            ? format_rupiah((int)($summary['total_revenue'] / $summary['total_transaksi']))
                            : 'Rp 0' ?>
                    </div>
                </div>
            </div>

            <!-- Distribusi Metode Bayar -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; margin-bottom:2rem;">
                <div class="card" style="text-align:center;">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Tunai</div>
                    <div style="font-size:1.25rem; font-weight:600;">
                        <?= $summary['total_revenue'] > 0
                            ? round(($summary['tunai'] / $summary['total_revenue']) * 100, 1) . '%'
                            : '0%' ?>
                    </div>
                    <div style="font-size:0.875rem; color:var(--text-muted);"><?= format_rupiah($summary['tunai']) ?></div>
                </div>
                <div class="card" style="text-align:center;">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Kartu</div>
                    <div style="font-size:1.25rem; font-weight:600;">
                        <?= $summary['total_revenue'] > 0
                            ? round(($summary['kartu'] / $summary['total_revenue']) * 100, 1) . '%'
                            : '0%' ?>
                    </div>
                    <div style="font-size:0.875rem; color:var(--text-muted);"><?= format_rupiah($summary['kartu']) ?></div>
                </div>
                <div class="card" style="text-align:center;">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">QRIS</div>
                    <div style="font-size:1.25rem; font-weight:600;">
                        <?= $summary['total_revenue'] > 0
                            ? round(($summary['qris'] / $summary['total_revenue']) * 100, 1) . '%'
                            : '0%' ?>
                    </div>
                    <div style="font-size:0.875rem; color:var(--text-muted);"><?= format_rupiah($summary['qris']) ?></div>
                </div>
                <div class="card" style="text-align:center;">
                    <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Transfer</div>
                    <div style="font-size:1.25rem; font-weight:600;">
                        <?= $summary['total_revenue'] > 0
                            ? round(($summary['transfer'] / $summary['total_revenue']) * 100, 1) . '%'
                            : '0%' ?>
                    </div>
                    <div style="font-size:0.875rem; color:var(--text-muted);"><?= format_rupiah($summary['transfer']) ?></div>
                </div>
            </div>

            <!-- Total breakdown -->
            <div class="card" style="max-width:500px;">
                <div style="font-size:1rem; font-weight:600; margin-bottom:1rem;">Total Semua Metode</div>
                <table style="margin-bottom:0;">
                    <tbody>
                        <tr>
                            <td style="padding:0.5rem 0; color:var(--text-muted);">Tunai</td>
                            <td style="padding:0.5rem 0; text-align:right; font-weight:500;"><?= format_rupiah($summary['tunai']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0; color:var(--text-muted);">Kartu</td>
                            <td style="padding:0.5rem 0; text-align:right; font-weight:500;"><?= format_rupiah($summary['kartu']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0; color:var(--text-muted);">QRIS</td>
                            <td style="padding:0.5rem 0; text-align:right; font-weight:500;"><?= format_rupiah($summary['qris']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0; color:var(--text-muted);">Transfer</td>
                            <td style="padding:0.5rem 0; text-align:right; font-weight:500;"><?= format_rupiah($summary['transfer']) ?></td>
                        </tr>
                        <tr style="border-top:2px solid var(--border-color);">
                            <td style="padding:0.75rem 0; font-weight:600;">Total</td>
                            <td style="padding:0.75rem 0; text-align:right; font-weight:700; color:var(--primary-color);"><?= format_rupiah($summary['total_revenue']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
