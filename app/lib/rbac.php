<?php
/**
 * Role-Based Access Control (RBAC).
 *
 * Role yang tersedia:
 *   - superadmin : akses penuh sistem
 *   - owner       : manage semua cabang & user, lihat semua laporan, set brand
 *   - admin       : kepala cabang, akses data cabang sendiri
 *   - kasir       : input transaksi & cetak struk, cek stok
 *   - keuangan    : lihat laporan keuangan, baca-only transaksi semua
 *
 * Fungsi-fungsi di modul ini digunakan di setiap controller/view
 * untuk memastikan user hanya mengakses apa yang diizinkan.
 */

/**
 * Cek apakah user memiliki salah satu role yang diperlukan.
 * Contoh: checkRole(['admin', 'superadmin'])
 */
function check_role(array $requiredRoles): bool
{
    $userRole = $_SESSION['user_role'] ?? null;
    if ($userRole === null) {
        return false;
    }
    return in_array($userRole, $requiredRoles, true);
}

/**
 * Cek apakah user diizinkan mengakses data milik cabang tertentu.
 * Superadmin & owner: selalu diizinkan.
 * Role terikat cabang (admin, kasir, keuangan): harus cocok.
 */
function check_cabang_access(?int $targetCabangId = null): bool
{
    $userRole = $_SESSION['user_role'] ?? null;

    if (in_array($userRole, ['superadmin', 'owner'], true)) {
        return true;
    }

    if (in_array($userRole, ['admin', 'kasir', 'keuangan'], true)) {
        $userCabangId = $_SESSION['user_cabang_id'] ?? null;
        if ($targetCabangId === null) {
            return true;   // ambil data milik cabang user sendiri
        }
        return $userCabangId !== null && $userCabangId === $targetCabangId;
    }

    return false;
}

/**
 * Middleware: lempar 403 kalau user tidak punya role.
 */
function require_role(array $requiredRoles): void
{
    if (!check_role($requiredRoles)) {
        http_response_code(403);
        echo 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.';
        exit;
    }
}

/**
 * Middleware: lempar 403 kalau user tidak punya akses ke cabang target.
 */
function require_cabang_access(?int $targetCabangId = null): void
{
    if (!check_cabang_access($targetCabangId)) {
        http_response_code(403);
        echo 'Akses ditolak. Data ini tidak tersedia untuk cabang Anda.';
        exit;
    }
}
