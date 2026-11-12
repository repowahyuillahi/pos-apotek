<?php
/**
 * Form tambah / edit cabang.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/CabangController.php';

$cabang = $cabang ?? null;
$action = $action ?? 'create';
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$pageTitle = $action === 'create' ? 'Tambah Cabang' : 'Edit Cabang';
$pageHeading = $action === 'create' ? 'Tambah Cabang Baru' : 'Edit Cabang';
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container" style="max-width:640px;">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;"><?= $pageHeading ?></h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Form <?= $action === 'create' ? 'tambah' : 'edit' ?> data cabang.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/owner?page=cabang&action=<?= $action ?>">
                <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'store' ?>">
                <?php if ($cabang): ?>
                    <input type="hidden" name="id" value="<?= $cabang['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="nama">Nama Cabang *</label>
                    <input type="text" id="nama" name="nama"
                        class="form-control <?= $flash && $flash['type'] === 'danger' ? 'is-invalid' : '' ?>"
                        value="<?= esc($cabang['nama'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3"><?= esc($cabang['alamat'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telepon">Telepon</label>
                    <input type="text" id="telepon" name="telepon"
                        class="form-control"
                        value="<?= esc($cabang['telepon'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control"
                        value="<?= esc($cabang['email'] ?? '') ?>"
                    >
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <?= $action === 'create' ? 'Simpan' : 'Update' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/owner?page=cabang" class="btn btn-secondary" style="flex:0 0 auto;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
