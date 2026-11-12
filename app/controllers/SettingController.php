<?php
/**
 * SettingController — manage brand settings.
 * Hanya diakses oleh superadmin & owner.
 *
 * Method:
 *   index()  → form setting brand
 *   update() → simpan setting brand
 */

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../models/SettingBrandModel.php';

require_role(['superadmin', 'owner']);
require_login();

$action = $_GET['action'] ?? 'index';
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] ?? '' === 'update') {
    try {
        $data = [
            'nama_apotek'    => trim($_POST['nama_apotek'] ?? ''),
            'primary_color'  => trim($_POST['primary_color'] ?? '#1a73e8'),
            'secondary_color'=> trim($_POST['secondary_color'] ?? '#ffffff'),
            'font_family'    => trim($_POST['font_family'] ?? 'Plus Jakarta Sans'),
            'tagline'        => trim($_POST['tagline'] ?? ''),
        ];

        // Upload logo jika ada
        if (!empty($_FILES['logo']['name'])) {
            $logoName = upload_file('logo', __DIR__ . '/../assets/img/logo', 2097152, [
                'image/png', 'image/jpeg', 'image/jpg'
            ]);
            $data['logo_path'] = $logoName;
        }

        if (empty($data['nama_apotek'])) {
            throw new RuntimeException('Nama apotek wajib diisi.');
        }

        SettingBrandModel::update($data);
        setFlash('success', 'Setting brand berhasil diperbarui.');
        header('Location: ' . BASE_URL . '/owner?page=brand');
        exit;
    } catch (RuntimeException $e) {
        setFlash('danger', $e->getMessage());
        header('Location: ' . BASE_URL . '/owner?page=brand');
        exit;
    }
}

// Render
require_once __DIR__ . '/../views/owner/brand_setting.php';
