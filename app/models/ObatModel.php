<?php
/**
 * ObatModel — CRUD untuk tabel obat.
 * Scope: hanya obat milik cabang yang diizinkan.
 */

class ObatModel
{
    public static function all(?int $cabangId = null, array $filters = []): array
    {
        $sql = "SELECT * FROM obat WHERE is_active = 1";
        $params = [];

        if ($cabangId !== null) {
            $sql .= " AND cabang_id = :cid";
            $params['cid'] = $cabangId;
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND kategori = :kategori";
            $params['kategori'] = $filters['kategori'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (nama LIKE :search OR kode LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY nama";
        return DB::query($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return DB::fetchOne("SELECT * FROM obat WHERE id = :id", ['id' => $id]);
    }

    public static function findByCode(string $kode, ?int $cabangId): ?array
    {
        return DB::fetchOne(
            "SELECT * FROM obat WHERE kode = :kode AND cabang_id = :cid AND is_active = 1",
            ['kode' => $kode, 'cid' => $cabangId]
        );
    }

    public static function create(array $data): int
    {
        return DB::insert('obat', $data);
    }

    public static function update(int $id, array $data): bool
    {
        // Kalau kode diubah, cek unique constraint
        if (isset($data['kode'])) {
            $existing = DB::fetchOne(
                "SELECT id FROM obat WHERE kode = :kode AND cabang_id = :cid AND id != :id",
                ['kode' => $data['kode'], 'cid' => $data['cabang_id'] ?? 0, 'id' => $id]
            );
            if ($existing) {
                throw new RuntimeException('Kode obat sudah digunakan untuk obat lain di cabang ini.');
            }
        }
        return DB::update('obat', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool
    {
        // Cek apakah obat masih dipakai di transaksi
        $usedCount = DB::fetchOne(
            "SELECT COUNT(*) as cnt FROM transaksi_detail td
             JOIN transaksi t ON td.transaksi_id = t.id
             WHERE td.obat_id = :oid AND t.is_closed = 0",
            ['oid' => $id]
        );
        if ($usedCount && $usedCount['cnt'] > 0) {
            throw new RuntimeException('Obat masih memiliki transaksi yang belum ditutup. Tidak bisa dihapus.');
        }
        return DB::delete('obat', 'id = :id', ['id' => $id]);
    }

    public static function lowStock(?int $cabangId, int $threshold = 5): array
    {
        $sql = "SELECT * FROM obat WHERE stok > 0 AND stok <= stok_minimal AND is_active = 1";
        $params = [];
        if ($cabangId !== null) {
            $sql .= " AND cabang_id = :cid";
            $params['cid'] = $cabangId;
        }
        $sql .= " ORDER BY stok ASC";
        return DB::query($sql, $params);
    }

    public static function getCategories(?int $cabangId): array
    {
        $sql = "SELECT DISTINCT kategori FROM obat WHERE is_active = 1 AND kategori IS NOT NULL AND kategori != ''";
        if ($cabangId !== null) {
            $sql .= " AND cabang_id = :cid";
        }
        $sql .= " ORDER BY kategori";
        $rows = DB::query($sql, $cabangId !== null ? ['cid' => $cabangId] : []);
        return array_column($rows, 'kategori');
    }
}
