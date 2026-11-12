<?php
/**
 * CabangModel — CRUD untuk tabel cabang.
 * Hanya diakses oleh superadmin & owner.
 */

class CabangModel
{
    public static function all(): array
    {
        return DB::query("SELECT * FROM cabang ORDER BY id");
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM cabang WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        return DB::insert('cabang', $data);
    }

    public static function update(int $id, array $data): bool
    {
        return DB::update('cabang', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        // Cek apakah ada user yang terikat ke cabang ini
        $userCount = DB::fetchOne(
            "SELECT COUNT(*) as cnt FROM user WHERE cabang_id = :cid",
            ['cid' => $id]
        );
        if ($userCount && $userCount['cnt'] > 0) {
            throw new RuntimeException('Tidak bisa menghapus cabang yang masih memiliki user.');
        }
        return DB::delete('cabang', 'id = :id', ['id' => $id]);
    }
}
