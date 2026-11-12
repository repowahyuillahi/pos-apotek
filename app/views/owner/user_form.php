<?php
/**
 * Form tambah / edit user.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/UserController.php';

$user    = $user ?? null;
$cabangs = $cabangs ?? [];
$roles   = $roles ?? UserModel::getRoles();
$action  = $action ?? 'create';
$flash   = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$pageHeading = $action === 'create' ? 'Tambah User Baru' : 'Edit User';
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container" style="max-width:640px;">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;"><?= $pageHeading ?></h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Form <?= $action === 'create' ? 'tambah' : 'edit' ?> data user.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/owner?page=user&action=<?= $action ?>">
                <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'store' ?>">
                <?php if ($user): ?>
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap"
                        class="form-control <?= $flash && $flash['type'] === 'danger' ? 'is-invalid' : '' ?>"
                        value="<?= esc($user['nama_lengkap'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username *</label>
                    <input type="text" id="username" name="username"
                        class="form-control"
                        value="<?= esc($user['username'] ?? '') ?>"
                        required
                    >
                </div>

                <?php if ($action === 'create' || empty($user['password_hash'])): ?>
                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" id="password" name="password"
                            class="form-control"
                            required
                        >
                        <div class="form-hint">Minimal 6 karakter.</div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="text-muted">(khususnya diisi jika ingin mengubah)</span></label>
                        <input type="password" id="password" name="password" class="form-control">
                        <div class="form-hint">Kosongkan jika tidak ingin mengubah password.</div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">— Pilih Role —</option>
                        <?php foreach ($roles as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($user['role'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cabang_id">Cabang</label>
                    <select id="cabang_id" name="cabang_id" class="form-control">
                        <option value="">— Tanpa Cabang —</option>
                        <?php foreach ($cabangs as $cab): ?>
                            <option value="<?= $cab['id'] ?>" <?= ($user['cabang_id'] ?? '') == $cab['id'] ? 'selected' : '' ?>>
                                <?= esc($cab['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Superadmin tidak perlu cabang. Admin & kasir perlu cabang.</div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <?= $action === 'create' ? 'Simpan' : 'Update' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/owner?page=user" class="btn btn-secondary" style="flex:0 0 auto;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
