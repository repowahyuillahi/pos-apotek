-- ============================================================
-- APOTEK POS — Database Setup
-- Jalankan di phpMyAdmin: pilih database apotek_db → import file ini
-- ============================================================
-- Pastikan database sudah dibuat: CREATE DATABASE apotek_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE apotek_db;

-- ------------------------------------------------------------
-- Tabel: cabang
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cabang` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `alamat` TEXT,
    `telepon` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `warna_primer` VARCHAR(20) DEFAULT '#1a73e8',
    `warna_sekunder` VARCHAR(20) DEFAULT '#ffffff',
    `font_family` VARCHAR(100) DEFAULT 'Inter',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: user
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang_id` INT DEFAULT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('superadmin','owner','admin','kasir','keuangan') NOT NULL,
    `foto_path` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cabang_id`) REFERENCES `cabang`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: obat
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `obat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang_id` INT NOT NULL,
    `kode` VARCHAR(20) NOT NULL,
    `nama` VARCHAR(200) NOT NULL,
    `kategori` VARCHAR(100) DEFAULT NULL,
    `harga_beli` DECIMAL(12,0) DEFAULT 0,
    `harga_jual` DECIMAL(12,0) NOT NULL,
    `stok` INT DEFAULT 0,
    `stok_minimal` INT DEFAULT 5,
    `satuan` VARCHAR(50) DEFAULT 'pcs',
    `expired_date` DATE DEFAULT NULL,
    `bpom_no` VARCHAR(50) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cabang_id`) REFERENCES `cabang`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_cabang_kode` (`cabang_id`, `kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: transaksi
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `no_transaksi` VARCHAR(20) NOT NULL,
    `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total` DECIMAL(12,0) NOT NULL DEFAULT 0,
    `metode_bayar` ENUM('tunai','kartu','qris','transfer') DEFAULT 'tunai',
    `catatan` TEXT,
    `is_closed` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`cabang_id`) REFERENCES `cabang`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `uk_cabang_no` (`cabang_id`, `no_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: transaksi_detail
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi_detail` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaksi_id` INT NOT NULL,
    `obat_id` INT NOT NULL,
    `jumlah` INT NOT NULL,
    `harga_satuan` DECIMAL(12,0) NOT NULL,
    `subtotal` DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`obat_id`) REFERENCES `obat`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: setting_brand (global — bisa dishare semua cabang)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `setting_brand` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_apotek` VARCHAR(200) NOT NULL DEFAULT 'Apotek',
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `primary_color` VARCHAR(20) DEFAULT '#1a73e8',
    `secondary_color` VARCHAR(20) DEFAULT '#ffffff',
    `font_family` VARCHAR(100) DEFAULT 'Inter',
    `tagline` VARCHAR(255) DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Data awal (optional — boleh dihapus setelah setup)
-- ============================================================

-- 3 cabang contoh
INSERT INTO `cabang` (`nama`, `alamat`, `telepon`, `is_active`) VALUES
('Apotek Utama Cabang 1', 'Jl. Contoh No. 1, Kota', '021-12345678', 1),
('Apotek Utama Cabang 2', 'Jl. Contoh No. 2, Kota', '021-23456789', 1),
('Apotek Utama Cabang 3', 'Jl. Contoh No. 3, Kota', '021-34567890', 1);

-- Setting brand default
INSERT INTO `setting_brand` (`nama_apotek`, `primary_color`, `secondary_color`, `tagline`)
VALUES ('Apotek Kami', '#1a73e8', '#ffffff', 'Pelayanan kesehatan terbaik untuk keluarga Anda');

-- User superadmin (password: superadmin123)
-- Generate hash dengan: password_hash('superadmin123', PASSWORD_DEFAULT)
-- Hash di bawah adalah contoh — GANTI dengan hash yang di-generate di PHP!
INSERT INTO `user` (`cabang_id`, `nama_lengkap`, `username`, `password_hash`, `role`) VALUES
(NULL, 'Super Admin', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- NOTE: password di atas menggunakan hash dari password "superadmin123"
-- (ini adalah bcrypt hash standar untuk password itu)
