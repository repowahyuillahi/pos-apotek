<?php
/**
 * ObatController — CRUD obat.
 * Diakses oleh admin (kepala cabang), owner, superadmin.
 * Scope: hanya obat milik cabang yang diizinkan.
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../models/ObatModel.php';

require_login();
require_role(['admin', 'owner', 'superadmin']);
// Cek akses cabang
$cabangId = $_SESSION['user_cabang_id'] ?? null;
if (check_role(['admin'])) {
    // Admin hanya boleh mengakses obat milik cabangnya
    if ($cabangId === null) {
        http_response_code(403);
        echo 'Error: User tidak memiliki cabang.';
        exit;
    }
}

$action = $_GET['action'] ?? 'index';
$id     = $_GET['id']   ?? null;
$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Filter untuk list
$filters = [];
if ($search !== '') $filters['search'] = $search;
if ($kategori !== '') $filters['kategori'] = $kategori;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'store') {
        try {
            $data = [
                'cabang_id'    => $cabangId,
                'kode'         => trim($_POST['kode'] ?? '') ?: strtoupper(substr(md5(uniqid()), 0, 8)),
                'nama'         => trim($_POST['nama'] ?? ''),
                'kategori'     => trim($_POST['kategori'] ?? ''),
                'harga_beli'   => (int)($_POST['harga_beli'] ?? 0),
                'harga_jual'   => (int)($_POST['harga_jual'] ?? 0),
                'stok'         => (int)($_POST['stok'] ?? 0),
                'stok_minimal' => (int)($_POST['stok_minimal'] ?? 5),
                'satuan'       => trim($_POST['satuan'] ?? 'pcs'),
                'expired_date' => $_POST['expired_date'] ?: null,
                'bpom_no'      => trim($_POST['bpom_no'] ?? ''),
            ];
            if (empty($data['nama']) || $data['harga_jual'] <= 0) {
                throw new RuntimeException('Nama dan harga jual wajib diisi.');
            }
            ObatModel::create($data);
            setFlash('success', 'Obat berhasil ditambahkan.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/admin?page=obat&action=create');
            exit;
        }
    }

    if ($postAction === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID obat tidak valid.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        }
        try {
            $data = [
                'nama'         => trim($_POST['nama'] ?? ''),
                'kategori'     => trim($_POST['kategori'] ?? ''),
                'harga_beli'   => (int)($_POST['harga_beli'] ?? 0),
                'harga_jual'   => (int)($_POST['harga_jual'] ?? 0),
                'stok'         => (int)($_POST['stok'] ?? 0),
                'stok_minimal' => (int)($_POST['stok_minimal'] ?? 5),
                'satuan'       => trim($_POST['satuan'] ?? 'pcs'),
                'expired_date' => $_POST['expired_date'] ?: null,
                'bpom_no'      => trim($_POST['bpom_no'] ?? ''),
                'cabang_id'    => $cabangId,
            ];
            if (empty($data['nama']) || $data['harga_jual'] <= 0) {
                throw new RuntimeException('Nama dan harga jual wajib diisi.');
            }
            ObatModel::update((int)$id, $data);
            setFlash('success', 'Obat berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/admin?page=obat&action=edit&id=' . $id);
            exit;
        }
    }

    if ($postAction === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID obat tidak valid.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        }
        try {
            ObatModel::delete((int)$id);
            setFlash('success', 'Obat berhasil dihapus.');
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin?page=obat');
        exit;
    }

    if ($postAction === 'adjustStock') {
        $id = $_POST['id'] ?? null;
        $delta = (int)($_POST['delta'] ?? 0);
        if (!$id || $delta === 0) {
            setFlash('danger', 'Data tidak valid.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        }
        try {
            $obat = ObatModel::find((int)$id);
            if (!$obat) throw new RuntimeException('Obat tidak ditemukan.');
            $newStok = max(0, ($obat['stok'] ?? 0) + $delta);
            ObatModel::update((int)$id, ['stok' => $newStok]);
            setFlash('success', 'Stok berhasil diupdate.');
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin?page=obat');
        exit;
    }
}

// Render view
switch ($action) {
    case 'create':
        $categories = ObatModel::getCategories($cabangId);
        require_once __DIR__ . '/../views/admin/obat_form.php';
        break;
    case 'edit':
        $obat = ObatModel::find((int)$id);
        if (!$obat) {
            setFlash('danger', 'Obat tidak ditemukan.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        }
        if ($cabangId && (int)$obat['cabang_id'] !== $cabangId) {
            http_response_code(403);
            echo 'Akses ditolak.';
            exit;
        }
        $categories = ObatModel::getCategories($cabangId);
        require_once __DIR__ . '/../views/admin/obat_form.php';
        break;
    case 'delete':
        $obat = ObatModel::find((int)$id);
        if (!$obat) {
            setFlash('danger', 'Obat tidak ditemukan.');
            header('Location: ' . BASE_URL . '/admin?page=obat');
            exit;
        }
        require_once __DIR__ . '/../views/admin/obat_delete_confirm.php';
        break;
    default:
        $obats = ObatModel::all($cabangId, $filters);
        $categories = ObatModel::getCategories($cabangId);
        require_once __DIR__ . '/../views/admin/obat_list.php';
        break;
}
