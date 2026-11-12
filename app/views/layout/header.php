<?php
/**
 * Header — navbar + brand loading.
 * Dipanggil di semua halaman yang memerlukan login.
 *
 * Load setting_brand dari DB → set CSS variables inline.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';

// Load brand settings (asumsi hanya ada 1 baris)
$brand = DB::fetchOne("SELECT * FROM setting_brand LIMIT 1") ?: [];
$brandName     = $brand['nama_apotek'] ?? 'Apotek';
$brandLogo     = $brand['logo_path'] ?? null;
$brandPrimary  = $brand['primary_color'] ?? '#1a73e8';
$brandSecondary = $brand['secondary_color'] ?? '#ffffff';
$brandFont     = $brand['font_family'] ?? 'Plus Jakarta Sans';
$brandTagline  = $brand['tagline'] ?? '';

// URL logo
$logoUrl = null;
if ($brandLogo) {
    $logoUrl = BASE_URL . '/assets/img/logo/' . $brandLogo;
}

// User info dari session
$user = current_user();
$userName = $user['user_nama'] ?? 'Pengguna';
$userRole = $user['user_role'] ?? '';
$cabangId = $user['user_cabang_id'] ?? null;

// Nama cabang jika ada
$cabangName = '';
if ($cabangId) {
    $c = DB::fetchOne("SELECT nama FROM cabang WHERE id = :id", ['id' => $cabangId]);
    $cabangName = $c['nama'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($brandName) ?> — POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        /* Brand CSS variables — di-set dari setting_brand */
        :root {
            --primary-color: <?= $brandPrimary ?>;
            --primary-dark: <?= $brandPrimary ?>;
            --secondary-color: <?= $brandSecondary ?>;
            --font-family: '<?= esc($brandFont) ?>', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container container">
            <!-- Brand -->
            <a href="<?= BASE_URL ?>/" class="navbar-brand">
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo <?= esc($brandName) ?>" class="navbar-logo">
                <?php endif; ?>
                <span class="navbar-brand-name"><?= esc($brandName) ?></span>
            </a>

            <!-- Right side: user info + logout -->
            <div class="navbar-right">
                <?php if ($user): ?>
                    <div class="navbar-user">
                        <span class="navbar-user-name"><?= esc($userName) ?></span>
                        <span class="navbar-user-role badge badge-primary"><?= esc(ucfirst($userRole)) ?></span>
                        <?php if ($cabangName): ?>
                            <span class="navbar-user-cabang text-muted">| <?= esc($cabangName) ?></span>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary btn-sm" onclick="event.preventDefault(); if(confirm('Logout?')) window.location='<?= BASE_URL ?>/logout.php';">
                            Keluar
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-sm">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <style>
        .navbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        .navbar-logo {
            height: 32px;
            width: auto;
        }
        .navbar-brand-name {
            font-size: 1.125rem;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        .navbar-user-name {
            font-weight: 500;
        }
    </style>

    <!-- Flash message (kalau ada) -->
    <?php $flash = AuthController::getFlash(); ?>
    <?php if ($flash): ?>
        <div style="padding: 0.75rem 20px; max-width: 1200px; margin: 0 auto;">
            <div class="alert alert-<?= esc($flash['type']) ?>">
                <?= esc($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>
