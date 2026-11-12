<?php
/**
 * Session helper.
 * Inisialisasi session aman, retrieve current user, require login.
 */

function session_start_secure(): void
{
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_role(array $requiredRoles): void
{
    require_login();
    $userRole = $_SESSION['user_role'] ?? null;
    if (!$userRole || !in_array($userRole, $requiredRoles, true)) {
        http_response_code(403);
        echo 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.';
        exit;
    }
}

function logout(): void
{
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
