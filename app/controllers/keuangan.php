<?php
/**
 * Keuangan Controller — dispatcher halaman keuangan.
 * Route berdasarkan $_GET['page'].
 *
 * Halaman:
 *   dashboard           → dashboard keuangan
 *   laporan_penjualan   → laporan penjualan
 *   laporan_keu         → laporan keuangan
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../lib/helper.php';

require_role(['keuangan', 'owner', 'superadmin']);
require_login();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/../views/keuangan/dashboard.php';
        break;

    case 'laporan_penjualan':
        require_once __DIR__ . '/../views/keuangan/laporan_penjualan.php';
        break;

    case 'laporan_keu':
        require_once __DIR__ . '/../views/keuangan/laporan_keu.php';
        break;

    default:
        require_once __DIR__ . '/../views/keuangan/dashboard.php';
        break;
}
