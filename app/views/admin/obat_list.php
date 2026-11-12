<?php
/**
 * Halaman list obat (Admin / Owner / Superadmin).
 * Scope: hanya obat milik cabang user.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/ObatController.php';

$obats       = $obats ?? [];
$categories  = $categories ?? [];
$search      = $_GET['search'] ?? '';
$kategori    = $_GET['kategori'] ?? '';
$flash       = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Hitung stok menipis
$lowStock = ObatModel::lowStock($cabangId);
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1 style="font-size:1.5rem; font-weight:700;">Data Obat</h1>
                    <p class="text-muted" style="margin-top:0.25rem;">Kelola katalog obat. <?= count($lowStock) ?> obat stok menipis.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin?page=obat&action=create" class="btn btn-primary">
                    + Tambah Obat
                </a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <form method="get" style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
                <input type="hidden" name="page" value="obat">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/obat..." value="<?= esc($search) ?>" style="width:240px;">
                <select name="kategori" class="form-control" style="width:180px;">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat) ?>" <?= $kategori === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
                <?php if ($search !== '' || $kategori !== ''): ?>
                    <a href="<?= BASE_URL ?>/admin?page=obat" class="btn btn-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>

            <div class="table-wrapper">
                <table style="min-width:800px;">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Stok</th>
                            <th>Expired</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($obats)): ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding:2rem; color:var(--text-muted);">
                                    Belum ada obat. Klik "Tambah Obat" untuk menambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($obats as $i => $ob): ?>
                                <?php $stokMenipis = ($ob['stok'] > 0 && $ob['stok'] <= $ob['stok_minimal']); ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?= esc($ob['kode']) ?></span></td>
                                        <td><strong><?= esc($ob['nama']) ?></strong></td>
                                        <td><?= esc($ob['kategori'] ?: '-') ?></td>
                                        <td class="text-right"><?= format_rupiah((int)$ob['harga_jual']) ?></td>
                                        <td class="text-right">
                                            <?php if ($stokMenipis): ?>
                                                <span class="badge badge-danger"><?= esc($ob['stok']) ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><?= esc($ob['stok']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $ob['expired_date'] ? esc(format_date($ob['expired_date'])) : '-' ?></td>
                                        <td>
                                            <?php if ($ob['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                                <a href="<?= BASE_URL ?>/admin?page=obat&action=edit&id=<?= $ob['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Hapus obat ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $ob['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Stok menipis alert -->
            <?php if (!empty($lowStock)): ?>
                <div class="alert alert-danger" style="margin-top:1.5rem;">
                    <strong>Peringatan:</strong> <?= count($lowStock) ?> obat dengan stok menipis.
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
