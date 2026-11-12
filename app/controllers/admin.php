<?php
/**
 * Admin Controller — dispatcher halaman admin (kepala cabang).
 * Route berdasarkan $_GET['page'].
 *
 * Halaman:
 *   dashboard → dashboard admin
 *   obat      → ObatController
 *   transaksi → list transaksi
 *   laporan   → laporan admin
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../lib/helper.php';

require_role(['admin']);
require_login();

// Admin terikat cabang — cek akses
$cabangId = $_SESSION['user_cabang_id'] ?? null;
if ($cabangId === null) {
    http_response_code(403);
    echo 'Error: Admin harus memiliki cabang.';
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/../views/admin/dashboard.php';
        break;

    case 'obat':
        require_once __DIR__ . '/../controllers/ObatController.php';
        break;

    case 'transaksi':
        require_once __DIR__ . '/../views/admin/transaksi_list.php';
        break;

    case 'laporan':
        require_once __DIR__ . '/../controllers/LaporanController.php';
        break;

    default:
        require_once __DIR__ . '/../views/admin/dashboard.php';
        break;
}
