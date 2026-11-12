<?php
/**
 * TransaksiModel — CRUD untuk transaksi & transaksi_detail.
 */

class TransaksiModel
{
    public static function all(?int $cabangId = null, array $filters = []): array
    {
        $sql = "SELECT t.*, u.nama_lengkap as kasir_nama
                FROM transaksi t
                JOIN user u ON t.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($cabangId !== null) {
            $sql .= " AND t.cabang_id = :cid";
            $params['cid'] = $cabangId;
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.tanggal) >= :start";
            $params['start'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.tanggal) <= :end";
            $params['end'] = $filters['end_date'];
        }

        $sql .= " ORDER BY t.tanggal DESC";
        return DB::query($sql, $params);
    }

    public static function find(int $id): ?array
    {
        $transaksi = DB::fetchOne(
            "SELECT t.*, u.nama_lengkap as kasir_nama
             FROM transaksi t
             JOIN user u ON t.user_id = u.id
             WHERE t.id = :id",
            ['id' => $id]
        );
        if ($transaksi) {
            $transaksi['details'] = DB::query(
                "SELECT td.*, o.nama, o.kode, o.harga_jual
                 FROM transaksi_detail td
                 JOIN obat o ON td.obat_id = o.id
                 WHERE td.transaksi_id = :tid",
                ['tid' => $id]
            );
        }
        return $transaksi;
    }

    public static function create(array $data): int
    {
        return DB::insert('transaksi', $data);
    }

    public static function addDetail(int $transaksiId, array $data): int
    {
        $data['transaksi_id'] = $transaksiId;
        return DB::insert('transaksi_detail', $data);
    }

    public static function getSummary(?int $cabangId, string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    COUNT(*) as total_transaksi,
                    SUM(total) as total_revenue,
                    SUM(CASE WHEN metode_bayar = 'tunai' THEN total ELSE 0 END) as tunai,
                    SUM(CASE WHEN metode_bayar = 'kartu' THEN total ELSE 0 END) as kartu,
                    SUM(CASE WHEN metode_bayar = 'qris' THEN total ELSE 0 END) as qris,
                    SUM(CASE WHEN metode_bayar = 'transfer' THEN total ELSE 0 END) as transfer
                FROM transaksi
                WHERE is_closed = 1";
        $params = [];

        if ($cabangId !== null) {
            $sql .= " AND cabang_id = :cid";
            $params['cid'] = $cabangId;
        }

        if ($startDate) {
            $sql .= " AND DATE(tanggal) >= :start";
            $params['start'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND DATE(tanggal) <= :end";
            $params['end'] = $endDate;
        }

        $row = DB::fetchOne($sql, $params);
        return [
            'total_transaksi' => (int)($row['total_transaksi'] ?? 0),
            'total_revenue'   => (int)($row['total_revenue'] ?? 0),
            'tunai'           => (int)($row['tunai'] ?? 0),
            'kartu'           => (int)($row['kartu'] ?? 0),
            'qris'            => (int)($row['qris'] ?? 0),
            'transfer'        => (int)($row['transfer'] ?? 0),
        ];
    }

    public static function getToday(?int $cabangId): array
    {
        return self::getSummary(
            $cabangId,
            date('Y-m-d'),
            date('Y-m-d')
        );
    }

    public static function generateNoTransaksi(?int $cabangId): string
    {
        // Ini dipanggil dari helper::generate_no_transaksi
        // tapi kita juga perlu versi model untuk konsistensi
        return generate_no_transaksi($cabangId ?? 0);
    }
}
