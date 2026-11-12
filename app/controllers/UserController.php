<?php
/**
 * UserController — CRUD user.
 * Hanya diakses oleh superadmin & owner.
 *
 * Method:
 *   index()    → list user
 *   create()   → form tambah user
 *   store()    → simpan user baru
 *   edit()     → form edit user
 *   update()   → update user
 *   delete()   → hapus user
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/CabangModel.php';

require_role(['superadmin', 'owner']);
require_login();

$action = $_GET['action'] ?? 'index';
$id     = $_GET['id']   ?? null;

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'store') {
        try {
            $cabangId = $_POST['cabang_id'] ?? null;
            $data = [
                'cabang_id'    => $cabangId ?: null,
                'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
                'username'     => trim($_POST['username'] ?? ''),
                'password'     => $_POST['password'] ?? '',
                'role'         => $_POST['role'] ?? 'kasir',
            ];
            if (empty($data['nama_lengkap']) || empty($data['username']) || empty($data['password'])) {
                throw new RuntimeException('Nama, username, dan password wajib diisi.');
            }
            if (strlen($data['password']) < 6) {
                throw new RuntimeException('Password minimal 6 karakter.');
            }
            UserModel::create($data);
            setFlash('success', 'User berhasil ditambahkan.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/owner?page=user&action=create');
            exit;
        }
    }

    if ($postAction === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID user tidak valid.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        }
        try {
            $data = [
                'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
                'username'     => trim($_POST['username'] ?? ''),
                'role'         => $_POST['role'] ?? 'kasir',
                'cabang_id'    => $_POST['cabang_id'] ?? null,
            ];
            // Password hanya di-update jika diisi
            $password = $_POST['password'] ?? '';
            if ($password !== '' && strlen($password) >= 6) {
                $data['password'] = $password;
            } elseif ($password !== '') {
                throw new RuntimeException('Password minimal 6 karakter.');
            }
            UserModel::update((int)$id, $data);
            setFlash('success', 'User berhasil diperbarui.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
            header('Location: ' . BASE_URL . '/owner?page=user&action=edit&id=' . $id);
            exit;
        }
    }

    if ($postAction === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            setFlash('danger', 'ID user tidak valid.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        }
        try {
            UserModel::delete((int)$id);
            setFlash('success', 'User berhasil dihapus.');
        } catch (RuntimeException $e) {
            setFlash('danger', $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/owner?page=user');
        exit;
    }
}

// Render view
switch ($action) {
    case 'create':
        $cabangs = CabangModel::all();
        $roles   = UserModel::getRoles();
        require_once __DIR__ . '/../views/owner/user_form.php';
        break;
    case 'edit':
        $user    = UserModel::find((int)$id);
        if (!$user) {
            setFlash('danger', 'User tidak ditemukan.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        }
        $cabangs = CabangModel::all();
        $roles   = UserModel::getRoles();
        require_once __DIR__ . '/../views/owner/user_form.php';
        break;
    case 'delete':
        $user = UserModel::find((int)$id);
        if (!$user) {
            setFlash('danger', 'User tidak ditemukan.');
            header('Location: ' . BASE_URL . '/owner?page=user');
            exit;
        }
        require_once __DIR__ . '/../views/owner/user_delete_confirm.php';
        break;
    default:
        require_once __DIR__ . '/../views/owner/user_list.php';
        break;
}
