<?php
/**
 * AuthController — handle login & logout.
 *
 * Login flow:
 *   1. Ambil user dari DB berdasarkan username
 *   2. Verifikasi password_hash
 *   3. Set session (user_id, nama, role, cabang_id, foto)
 *   4. Redirect ke halaman sesuai role
 *
 * Sesuai spec: role superadmin, owner, admin, kasir, keuangan.
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';

class AuthController
{
    /**
     * Coba login. Return true jika berhasil.
     */
    public static function login(string $username, string $password): bool
    {
        $user = DB::fetchOne(
            "SELECT id, cabang_id, nama_lengkap, username, password_hash, role, foto_path
             FROM user
             WHERE username = :username AND is_active = 1",
            ['username' => $username]
        );

        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Store session
        $_SESSION['user_id']     = (int) $user['id'];
        $_SESSION['user_nama']   = $user['nama_lengkap'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_cabang_id'] = $user['cabang_id'];
        $_SESSION['user_foto']   = $user['foto_path'];

        // Regenerate session ID after login (security)
        session_regenerate_id(true);

        return true;
    }

    /**
     * Ambil URL redirect after login berdasarkan role.
     */
    public static function redirectAfterLogin(): string
    {
        $role = $_SESSION['user_role'] ?? null;

        return match ($role) {
            'superadmin', 'owner' => '/owner',
            'admin'               => '/admin',
            'kasir'               => '/pos',
            'keuangan'            => '/keuangan',
            default               => '/login',
        };
    }

    /**
     * Logout — unload session, redirect ke login.
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    /**
     * Build flash message (simpan di session, ambil satu kali).
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,     // 'success' | 'danger' | 'info'
            'message' => $message,
        ];
    }

    public static function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
