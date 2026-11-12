<?php
/**
 * CabangController — CRUD cabang.
 * Hanya diakses oleh superadmin & owner.
 *
 * Method:
 *   index()    → list cabang
 *   create()   → form tambah cabang
 *   store()    → simpan cabang baru
 *   edit()     → form edit cabang
 *   update()   → update cabang
 *   delete()   → hapus cabang
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../models/CabangModel.php';

require_role(['superadmin', 'owner']);
require_login();

$action = $_GET['action'] ?? 'index';
$id     = $_GET['id']   ?? null;

// Flash message helper
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'store') {
        try {
            $data = [
                'nama'          => trim($_POST['nama'] ?? ''),
                'alamat'        => trim($_POST['alamat'] ?? ''),
                'telepon'       => trim($_POST['telepon'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
            ];
            if (empty($data['nama'])) {
                throw new RuntimeException('Nama cabang wajib diisi.');
            }
            CabangModel::create($data);
            setFlash('success', 'Cabang berhasil ditambahkan.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/owner?page=cabang&action=create');
            exit;
        }
    }

    if ($postAction === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID cabang tidak valid.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        }
        try {
            $data = [
                'nama'          => trim($_POST['nama'] ?? ''),
                'alamat'        => trim($_POST['alamat'] ?? ''),
                'telepon'       => trim($_POST['telepon'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
            ];
            if (empty($data['nama'])) {
                throw new RuntimeException('Nama cabang wajib diisi.');
            }
            CabangModel::update((int)$id, $data);
            setFlash('success', 'Cabang berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/owner?page=cabang&action=edit&id=' . $id);
            exit;
        }
    }

    if ($postAction === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID cabang tidak valid.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        }
        try {
            CabangModel::delete((int)$id);
            setFlash('success', 'Cabang berhasil dihapus.');
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/owner?page=cabang');
        exit;
    }

    if ($postAction === 'uploadLogo') {
        // Logo di-handle di setting brand, bukan di sini
        // Kalau dipakai, redirect ke brand setting
        setFlash('info', 'Upload logo dilakukan di halaman Setting Brand.');
        header('Location: ' . BASE_URL . '/owner?page=brand');
        exit;
    }
}

// Render view berdasarkan action
switch ($action) {
    case 'create':
        require_once __DIR__ . '/../views/owner/cabang_form.php';
        break;
    case 'edit':
        $cabang = CabangModel::find((int)$id);
        if (!$cabang) {
            setFlash('danger', 'Cabang tidak ditemukan.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        }
        require_once __DIR__ . '/../views/owner/cabang_form.php';
        break;
    case 'delete':
        // Handler POST, tapi kalau GET → konfirmasi
        $cabang = CabangModel::find((int)$id);
        if (!$cabang) {
            setFlash('danger', 'Cabang tidak ditemukan.');
            header('Location: ' . BASE_URL . '/owner?page=cabang');
            exit;
        }
        require_once __DIR__ . '/../views/owner/cabang_delete_confirm.php';
        break;
    default:
        require_once __DIR__ . '/../views/owner/cabang_list.php';
        break;
}
