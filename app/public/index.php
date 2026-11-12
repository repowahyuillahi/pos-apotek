<?php
/**
 * Front controller / router sederhana.
 * Semua request masuk di sini, lalu diarahkan ke view yang sesuai.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';

session_start_secure();

// Path relatif dari base URL
$scriptName = dirname($_SERVER['SCRIPT_NAME']);       // contoh: /apotek
$requestUri = $_SERVER['REQUEST_URI'];
$path       = parse_url($requestUri, PHP_URL_PATH);
$relative   = substr($path, strlen($scriptName));

// Normalisasi: hilangkan trailing slash (kecuali root)
$relative = '/' === $relative ? '/' : rtrim($relative, '/');

// Tabel routing: path → file view atau closure
$routes = [
    '/'          => __DIR__ . '/../views/landing/index.php',
    '/login'     => __DIR__ . '/../views/login/login.php',
    '/logout'    => function (): void { require_login(); logout(); },
    '/pos'       => __DIR__ . '/../controllers/pos.php',
    '/pos/cetak' => __DIR__ . '/../views/pos/cetak.php',
    '/admin'     => __DIR__ . '/../controllers/admin.php',
    '/owner'     => __DIR__ . '/../controllers/owner.php',
    '/keuangan'  => __DIR__ . '/../controllers/keuangan.php',
];

// Jika path belum terdaftar, fallback ke landing page
if (!isset($routes[$relative])) {
    $relative = '/';
}

// Eksekusi route
$response = $routes[$relative];

if (is_callable($response)) {
    $response();
    exit;
}

if (is_string($response) && file_exists($response)) {
    require_once $response;
    exit;
}

http_response_code(404);
echo 'Halaman tidak ditemukan.';
