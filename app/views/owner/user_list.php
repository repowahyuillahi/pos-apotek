<?php
/**
 * Halaman list user (Owner / Superadmin).
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/UserController.php';

$users   = UserModel::all();
$roles   = UserModel::getRoles();
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
                    <h1 style="font-size:1.5rem; font-weight:700;">Data User</h1>
                    <p class="text-muted" style="margin-top:0.25rem;">Kelola user sistem.</p>
                </div>
                <a href="<?= BASE_URL ?>/owner?page=user&action=create" class="btn btn-primary">
                    + Tambah User
                </a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table style="min-width:700px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Cabang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding:2rem; color:var(--text-muted);">
                                    Belum ada user. Klik "Tambah User" untuk menambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $i => $u): ?>
                                <tr>
                                    <td class="text-center"><?= esc($i + 1) ?></td>
                                    <td><strong><?= esc($u['nama_lengkap']) ?></strong></td>
                                    <td><?= esc($u['username']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= match ($u['role']) {
                                            'superadmin' => 'primary',
                                            'owner' => 'primary',
                                            'admin' => 'success',
                                            'kasir' => 'muted',
                                            'keuangan' => 'muted',
                                            default => 'muted'
                                        } ?>">
                                            <?= esc($roles[$u['role']] ?? $u['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($u['cabang_nama'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem;">
                                            <a href="<?= BASE_URL ?>/owner?page=user&action=edit&id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                            <?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Hapus user ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            <?php endif; ?>
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
