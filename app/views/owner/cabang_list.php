<?php
/**
 * Halaman list cabang (Owner / Superadmin).
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/CabangController.php';

$cabangs = CabangModel::all();
$flash   = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1 style="font-size:1.5rem; font-weight:700;">Data Cabang</h1>
                    <p class="text-muted" style="margin-top:0.25rem;">Kelola cabang apotek.</p>
                </div>
                <a href="<?= BASE_URL ?>/owner?page=cabang&action=create" class="btn btn-primary">
                    + Tambah Cabang
                </a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table style="min-width:600px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cabangs)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding:2rem; color:var(--text-muted);">
                                    Belum ada cabang. Klik "Tambah Cabang" untuk menambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cabangs as $i => $cab): ?>
                                <tr>
                                    <td class="text-center"><?= esc($i + 1) ?></td>
                                    <td><strong><?= esc($cab['nama']) ?></strong></td>
                                    <td><?= esc($cab['alamat']) ?></td>
                                    <td><?= esc($cab['telepon']) ?></td>
                                    <td><?= esc($cab['email']) ?></td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem;">
                                            <a href="<?= BASE_URL ?>/owner?page=cabang&action=edit&id=<?= $cab['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Hapus cabang ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $cab['id'] ?>">
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
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
