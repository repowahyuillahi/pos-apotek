<?php
/**
 * Setting Brand — form update nama, logo, warna, font.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/SettingController.php';

$brand = SettingBrandModel::get();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container" style="max-width:640px;">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;">Setting Brand</h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Atur nama, logo, warna, dan font untuk seluruh sistem.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/owner?page=brand">
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label class="form-label" for="nama_apotek">Nama Apotek *</label>
                    <input type="text" id="nama_apotek" name="nama_apotek"
                        class="form-control <?= $flash && $flash['type'] === 'danger' ? 'is-invalid' : '' ?>"
                        value="<?= esc($brand['nama_apotek'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="logo">Logo</label>
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/png,image/jpeg,image/jpg">
                    <?php if (!empty($brand['logo_path'])): ?>
                        <div style="margin-top:0.5rem;">
                            <img src="<?= BASE_URL ?>/assets/img/logo/<?= esc($brand['logo_path']) ?>"
                                 style="height:60px; object-fit:contain;" alt="Logo saat ini">
                            <div class="form-hint" style="margin-top:0.25rem;">Logo saat ini. Upload baru untuk mengganti.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="primary_color">Warna Primer</label>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <input type="color" id="primary_color" name="primary_color"
                                value="<?= $brand['primary_color'] ?? '#1a73e8' ?>"
                                style="width:60px; height:40px; padding:2px; border:1px solid var(--border-color); border-radius:6px; cursor:pointer;"
                            >
                            <input type="text" class="form-control" value="<?= esc($brand['primary_color'] ?? '#1a73e8') ?>" style="flex:1;">
                        </div>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="secondary_color">Warna Sekunder</label>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <input type="color" id="secondary_color" name="secondary_color"
                                value="<?= $brand['secondary_color'] ?? '#ffffff' ?>"
                                style="width:60px; height:40px; padding:2px; border:1px solid var(--border-color); border-radius:6px; cursor:pointer;"
                            >
                            <input type="text" class="form-control" value="<?= esc($brand['secondary_color'] ?? '#ffffff') ?>" style="flex:1;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="font_family">Font Family</label>
                    <select id="font_family" name="font_family" class="form-control">
                        <option value="Plus Jakarta Sans" <?= ($brand['font_family'] ?? '') === 'Plus Jakarta Sans' ? 'selected' : '' ?>>Plus Jakarta Sans</option>
                        <option value="Inter" <?= ($brand['font_family'] ?? '') === 'Inter' ? 'selected' : '' ?>>Inter</option>
                        <option value="Roboto" <?= ($brand['font_family'] ?? '') === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                        <option value="Poppins" <?= ($brand['font_family'] ?? '') === 'Poppins' ? 'selected' : '' ?>>Poppins</option>
                        <option value="Nunito" <?= ($brand['font_family'] ?? '') === 'Nunito' ? 'selected' : '' ?>>Nunito</option>
                        <option value="Arial" <?= ($brand['font_family'] ?? '') === 'Arial' ? 'selected' : '' ?>>Arial</option>
                    </select>
                    <div class="form-hint">Font yang digunakan di seluruh sistem.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tagline">Tagline</label>
                    <input type="text" id="tagline" name="tagline"
                        class="form-control"
                        value="<?= esc($brand['tagline'] ?? '') ?>"
                        placeholder="Mis. Pelayanan kesehatan terbaik untuk keluarga Anda"
                    >
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Simpan Setting</button>
                    <a href="<?= BASE_URL ?>/owner?page=dashboard" class="btn btn-secondary" style="flex:0 0 auto;">Kembali</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
