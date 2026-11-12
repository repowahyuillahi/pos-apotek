<?php
/**
 * LaporanController — laporan penjualan & keuangan.
 *
 * Diakses oleh: admin (kepala cabang), keuangan, owner, superadmin
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
require_once __DIR__ . '/../models/ObatModel.php';

require_login();

// Role & cabang
$userRole   = $_SESSION['user_role'] ?? '';
$cabangId  = $_SESSION['user_cabang_id'] ?? null;

// Filter default: hari ini
$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate   = $_GET['end']   ?? date('Y-m-d');

// Validasi: owner & superadmin bisa lihat semua, admin hanya cabang sendiri
if (check_role(['admin'])) {
    if ($cabangId === null) {
        http_response_code(403);
        echo 'Error: Admin harus memiliki cabang.';
        exit;
    }
    $filters = ['start_date' => $startDate, 'end_date' => $endDate];
    $summary     = TransaksiModel::getSummary($cabangId, $startDate, $endDate);
    $transactions = TransaksiModel::all($cabangId, $filters);
} else {
    // owner, superadmin, keuangan — bisa lihat semua
    $summary     = TransaksiModel::getSummary(null, $startDate, $endDate);
    $transactions = TransaksiModel::all(null, ['start_date' => $startDate, 'end_date' => $endDate]);
}

// Top produk (untuk admin & owner)
$topProducts = [];
if (check_role(['admin', 'owner', 'superadmin'])) {
    $topProducts = DB::query(
        "SELECT o.nama, o.kode, SUM(td.jumlah) as total_terjual, SUM(td.subtotal) as total_revenue
         FROM transaksi_detail td
         JOIN obat o ON td.obat_id = o.id
         JOIN transaksi t ON td.transaksi_id = t.id
         WHERE t.is_closed = 1
           AND DATE(t.tanggal) >= :start
           AND DATE(t.tanggal) <= :end"
        . ($cabangId ? " AND t.cabang_id = :cid" : ""),
        array_merge(
            ['start' => $startDate, 'end' => $endDate],
            $cabangId ? ['cid' => $cabangId] : []
        )
    );
    usort($topProducts, function($a, $b) {
        return ($b['total_terjual'] ?? 0) <=> ($a['total_terjual'] ?? 0);
    });
    $topProducts = array_slice($topProducts, 0, 5);
}

// Tampilkan view sesuai role
if (check_role(['admin'])) {
    require_once __DIR__ . '/../views/admin/laporan.php';
} elseif (check_role(['owner', 'superadmin'])) {
    require_once __DIR__ . '/../views/owner/laporan.php';
} else {
    require_once __DIR__ . '/../views/keuangan/laporan_penjualan.php';
}
