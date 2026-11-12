<?php
/**
 * UserModel — CRUD untuk tabel user.
 * Password di-hash sebelum disimpan.
 * Hanya diakses oleh superadmin & owner.
 */

class UserModel
{
    public static function all(?int $cabangId = null): array
    {
        if ($cabangId !== null) {
            return DB::query(
                "SELECT u.*, c.nama as cabang_nama FROM user u
                 LEFT JOIN cabang c ON u.cabang_id = c.id
                 WHERE u.is_active = 1
                 ORDER BY u.role, u.nama_lengkap"
            );
        }
        return DB::query(
            "SELECT u.*, c.nama as cabang_nama FROM user u
             LEFT JOIN cabang c ON u.cabang_id = c.id
             WHERE u.is_active = 1
             ORDER BY u.role, u.nama_lengkap"
        );
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne(
            "SELECT u.*, c.nama as cabang_nama FROM user u
             LEFT JOIN cabang c ON u.cabang_id = c.id
             WHERE u.id = :id",
            ['id' => $id]
        );
    }

    public static function create(array $data): int
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return DB::insert('user', $data);
    }

    public static function update(int $id, array $data): bool
    {
        // Jika password dikirim, hash dulu
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        } else {
            unset($data['password']);
        }
        return DB::update('user', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        // Jangan hapus user yang sedang login
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
            throw new RuntimeException('Tidak bisa menghapus user yang sedang login.');
        }
        return DB::delete('user', 'id = :id', ['id' => $id]);
    }

    public static function getRoles(): array
    {
        return [
            'superadmin' => 'Superadmin',
            'owner'      => 'Owner',
            'admin'      => 'Admin (Kepala Cabang)',
            'kasir'      => 'Kasir',
            'keuangan'   => 'Keuangan',
        ];
    }
}
