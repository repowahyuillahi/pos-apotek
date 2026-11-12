<?php
/**
 * Sidebar navigasi — menyesuaikan menu berdasarkan role user.
 *
 * Role menu:
 *   superadmin / owner → Dashboard, Cabang, User, Obat, Transaksi, Laporan, Setting Brand
 *   admin              → Dashboard, Obat, Transaksi, Laporan (cabang sendiri)
 *   kasir              → POS (halaman utama, handled by pos/index.php)
 *   keuangan           → Dashboard, Laporan Penjualan, Laporan Keuangan
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';

$user       = current_user();
$userRole   = $user['user_role'] ?? '';
$cabangId  = $user['user_cabang_id'] ?? null;

// Active page (dari query string atau default)
$activePage = $_GET['page'] ?? '';
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <?php if (check_role(['superadmin', 'owner'])): ?>
            <a href="<?= BASE_URL ?>/owner?page=dashboard" class="sidebar-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <span class="sidebar-icon">📊</span> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/owner?page=cabang" class="sidebar-item <?= $activePage === 'cabang' ? 'active' : '' ?>">
                <span class="sidebar-icon">🏢</span> Cabang
            </a>
            <a href="<?= BASE_URL ?>/owner?page=user" class="sidebar-item <?= $activePage === 'user' ? 'active' : '' ?>">
                <span class="sidebar-icon">👥</span> User
            </a>
            <div class="sidebar-divider"></div>
            <a href="<?= BASE_URL ?>/admin?page=obat" class="sidebar-item <?= $activePage === 'obat' ? 'active' : '' ?>">
                <span class="sidebar-icon">💊</span> Obat
            </a>
            <a href="<?= BASE_URL ?>/admin?page=transaksi" class="sidebar-item <?= $activePage === 'transaksi' ? 'active' : '' ?>">
                <span class="sidebar-icon">🧾</span> Transaksi
            </a>
            <div class="sidebar-divider"></div>
            <a href="<?= BASE_URL ?>/owner?page=laporan" class="sidebar-item <?= $activePage === 'laporan' ? 'active' : '' ?>">
                <span class="sidebar-icon">📈</span> Laporan
            </a>
            <a href="<?= BASE_URL ?>/owner?page=brand" class="sidebar-item <?= $activePage === 'brand' ? 'active' : '' ?>">
                <span class="sidebar-icon">🎨</span> Setting Brand
            </a>
        <?php elseif (check_role(['admin'])): ?>
            <a href="<?= BASE_URL ?>/admin?page=dashboard" class="sidebar-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <span class="sidebar-icon">📊</span> Dashboard
            </a>
            <div class="sidebar-divider"></div>
            <a href="<?= BASE_URL ?>/admin?page=obat" class="sidebar-item <?= $activePage === 'obat' ? 'active' : '' ?>">
                <span class="sidebar-icon">💊</span> Obat
            </a>
            <a href="<?= BASE_URL ?>/admin?page=transaksi" class="sidebar-item <?= $activePage === 'transaksi' ? 'active' : '' ?>">
                <span class="sidebar-icon">🧾</span> Transaksi
            </a>
            <a href="<?= BASE_URL ?>/admin?page=laporan" class="sidebar-item <?= $activePage === 'laporan' ? 'active' : '' ?>">
                <span class="sidebar-icon">📈</span> Laporan
            </a>
        <?php elseif (check_role(['keuangan'])): ?>
            <a href="<?= BASE_URL ?>/keuangan?page=dashboard" class="sidebar-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <span class="sidebar-icon">📊</span> Dashboard
            </a>
            <div class="sidebar-divider"></div>
            <a href="<?= BASE_URL ?>/keuangan?page=laporan_penjualan" class="sidebar-item <?= $activePage === 'laporan_penjualan' ? 'active' : '' ?>">
                <span class="sidebar-icon">📈</span> Laporan Penjualan
            </a>
            <a href="<?= BASE_URL ?>/keuangan?page=laporan_keu" class="sidebar-item <?= $activePage === 'laporan_keu' ? 'active' : '' ?>">
                <span class="sidebar-icon">💰</span> Laporan Keuangan
            </a>
        <?php endif; ?>
    </nav>
</aside>

<style>
    .sidebar {
        width: 240px;
        min-height: calc(100vh - 60px);
        background: var(--bg-card);
        border-right: 1px solid var(--border-color);
        padding: 1rem 0;
        position: fixed;
        top: 60px;
        left: 0;
        overflow-y: auto;
    }
    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0 0.75rem;
    }
    .sidebar-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
        border-radius: 6px;
        font-size: 0.9rem;
        color: var(--text-muted);
        transition: background 0.15s, color 0.15s;
    }
    .sidebar-item:hover {
        background: var(--bg-color);
        color: var(--text-color);
    }
    .sidebar-item.active {
        background: var(--primary-color);
        color: #fff;
    }
    .sidebar-icon {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }
    .sidebar-divider {
        height: 1px;
        background: var(--border-color);
        margin: 0.5rem 0.75rem;
    }
    .sidebar-item .badge {
        margin-left: auto;
    }
</style>
