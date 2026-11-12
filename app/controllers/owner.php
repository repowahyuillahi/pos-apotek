<?php
/**
 * Owner Controller — dispatcher halaman owner.
 * Route berdasarkan $_GET['page'].
 *
 * Halaman:
 *   dashboard → dashboard owner
 *   cabang    → CabangController
 *   user      → UserController
 *   laporan   → laporan owner
 *   brand     → setting brand
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../lib/helper.php';

require_role(['superadmin', 'owner']);
require_login();

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        // Dashboard sederhana — redirect ke halaman overview
        require_once __DIR__ . '/../views/owner/dashboard.php';
        break;

    case 'cabang':
        require_once __DIR__ . '/../controllers/CabangController.php';
        break;

    case 'user':
        require_once __DIR__ . '/../controllers/UserController.php';
        break;

    case 'laporan':
        require_once __DIR__ . '/../controllers/LaporanController.php';
        break;

    case 'brand':
        require_once __DIR__ . '/../controllers/SettingController.php';
        break;

    default:
        require_once __DIR__ . '/../views/owner/dashboard.php';
        break;
}
