<?php
/**
 * SettingBrandModel — CRUD untuk tabel setting_brand.
 * Hanya diakses oleh superadmin & owner.
 */

class SettingBrandModel
{
    public static function get(): ?array
    {
        return DB::fetchOne("SELECT * FROM setting_brand LIMIT 1");
    }

    public static function update(array $data): bool
    {
        // Pastikan tabel ada minimal 1 row
        $existing = self::get();
        if ($existing === null) {
            return DB::insert('setting_brand', $data);
        }
        return DB::update('setting_brand', $data, 'id = :id', ['id' => $existing['id']]);
    }
}
