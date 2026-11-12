<?php
/**
 * Form tambah / edit obat.
 */
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../controllers/ObatController.php';

$obat       = $obat ?? null;
$categories = $categories ?? [];
$action      = $action ?? 'create';
$flash       = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$pageHeading = $action === 'create' ? 'Tambah Obat Baru' : 'Edit Obat';
?>
<?= require_once __DIR__ . '/../views/layout/header.php' ?>

<div class="app-layout" style="display:flex; min-height:calc(100vh - 60px);">
    <?= require_once __DIR__ . '/../views/layout/sidebar.php' ?>

    <main class="main-content" style="flex:1; margin-left:240px; padding:2rem; background:var(--bg-color);">
        <div class="container" style="max-width:640px;">
            <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem;"><?= $pageHeading ?></h1>
            <p class="text-muted" style="margin-bottom:1.5rem;">Form <?= $action === 'create' ? 'tambah' : 'edit' ?> data obat.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>" style="margin-bottom:1.5rem;">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/admin?page=obat&action=<?= $action ?>">
                <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'store' ?>">
                <?php if ($obat): ?>
                    <input type="hidden" name="id" value="<?= $obat['id'] ?>">
                <?php endif; ?>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="kode">Kode Obat</label>
                        <input type="text" id="kode" name="kode"
                            class="form-control"
                            value="<?= esc($obat['kode'] ?? '') ?>"
                            <?= $action === 'create' ? 'autofocus' : '' ?>
                        >
                        <?php if ($action === 'create'): ?>
                            <div class="form-hint">Otomatis terisi. Bisa diganti.</div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="flex:2;">
                        <label class="form-label" for="nama">Nama Obat *</label>
                        <input type="text" id="nama" name="nama"
                            class="form-control <?= $flash && $flash['type'] === 'danger' ? 'is-invalid' : '' ?>"
                            value="<?= esc($obat['nama'] ?? '') ?>"
                            required
                            <?= $action === 'create' ? 'autofocus' : '' ?>
                        >
                    </div>
                </div>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">— Pilih Kategori —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat) ?>" <?= ($obat['kategori'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= esc($cat) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="Lainnya" <?= ($obat['kategori'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="satuan">Satuan</label>
                        <select id="satuan" name="satuan" class="form-control">
                            <option value="pcs" <?= ($obat['satuan'] ?? 'pcs') === 'pcs' ? 'selected' : '' ?>>Pcs</option>
                            <option value="strip" <?= ($obat['satuan'] ?? '') === 'strip' ? 'selected' : '' ?>>Strip</option>
                            <option value="botol" <?= ($obat['satuan'] ?? '') === 'botol' ? 'selected' : '' ?>>Botol</option>
                            <option value="box" <?= ($obat['satuan'] ?? '') === 'box' ? 'selected' : '' ?>>Box</option>
                            <option value="tablet" <?= ($obat['satuan'] ?? '') === 'tablet' ? 'selected' : '' ?>>Tablet</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="harga_beli">Harga Beli</label>
                        <input type="number" id="harga_beli" name="harga_beli" min="0"
                            class="form-control"
                            value="<?= esc($obat['harga_beli'] ?? 0) ?>"
                        >
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="harga_jual">Harga Jual *</label>
                        <input type="number" id="harga_jual" name="harga_jual" min="1"
                            class="form-control <?= $flash && $flash['type'] === 'danger' ? 'is-invalid' : '' ?>"
                            value="<?= esc($obat['harga_jual'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" min="0"
                            class="form-control"
                            value="<?= esc($obat['stok'] ?? 0) ?>"
                        >
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="stok_minimal">Stok Minimal</label>
                        <input type="number" id="stok_minimal" name="stok_minimal" min="0"
                            class="form-control"
                            value="<?= esc($obat['stok_minimal'] ?? 5) ?>"
                        >
                    </div>
                </div>

                <div style="display:flex; gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="expired_date">Expired Date</label>
                        <input type="date" id="expired_date" name="expired_date"
                            class="form-control"
                            value="<?= esc($obat['expired_date'] ?? '') ?>"
                        >
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" for="bpom_no">BPOM No</label>
                        <input type="text" id="bpom_no" name="bpom_no"
                            class="form-control"
                            value="<?= esc($obat['bpom_no'] ?? '') ?>"
                        >
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <?= $action === 'create' ? 'Simpan' : 'Update' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/admin?page=obat" class="btn btn-secondary" style="flex:0 0 auto;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<?= require_once __DIR__ . '/../views/layout/footer.php' ?>
