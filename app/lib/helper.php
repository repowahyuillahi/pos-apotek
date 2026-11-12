<?php
/**
 * Helper functions.
 * Format Rupiah, tanggal, escape output, CSRF, base URL.
 */

/**
 * Base URL aplikasi.
 */
function base_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Format angka ke Rupiah: Rp 1.234.567
 */
function format_rupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal YYYY-MM-DD → DD/MM/YYYY
 */
function format_date(string $date): string
{
    try {
        return date('d/m/Y', strtotime($date));
    } catch (\Throwable) {
        return $date;
    }
}

/**
 * Format datetime → DD/MM/YYYY HH:MM
 */
function format_datetime(string $datetime): string
{
    try {
        return date('d/m/Y H:i', strtotime($datetime));
    } catch (\Throwable) {
        return $datetime;
    }
}

/**
 * Escape HTML output.
 */
function esc($str): string
{
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate & store CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token.
 */
function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Generate no transaksi: TRX-YYYYMMDD-NNN
 */
function generate_no_transaksi(int $cabangId): string
{
    $date = date('Ymd');
    $count = (int) DB::fetchOne(
        'SELECT COUNT(*) as cnt FROM transaksi WHERE cabang_id = :cid AND tanggal >= :today',
        ['cid' => $cabangId, 'today' => date('Y-m-d')]
    )['cnt'] ?? 0;
    return sprintf('TRX-%s-%03d', $date, $count + 1);
}

/**
 * Upload file helper.
 * Returns saved filename or throws.
 */
function upload_file(string $inputName, string $uploadDir, int $maxSize = 2097152, array $allowed = ['image/png', 'image/jpeg', 'image/jpg']): string
{
    if (empty($_FILES[$inputName]['name'])) {
        return '';
    }
    $file = $_FILES[$inputName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal: ' . $file['error']);
    }
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File terlalu besar (maks 2MB).');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Tipe file tidak diperbolehkan.');
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(8)) . '.' . ($ext ?: 'png');
    $dest = $uploadDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Gagal memindahkan file.');
    }
    return $name;
}
