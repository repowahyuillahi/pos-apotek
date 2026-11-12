# Design Spec: App POS Apotek + Landing Page

**Dibuat:** 2026-11-12
**Status:** Draft untuk review

---

## 1. Intent & Konteks

**Tujuan:** Membangun aplikasi Point-of-Sale (POS) untuk apotek dengan 3 cabang, plus landing page promosi.

**Pengguna:**
- Kasir — input transaksi, cetak struk, cek stok
- Admin (kepala cabang) — manage obat, lihat transaksi & laporan cabang sendiri
- Keuangan — lihat transaksi semua cabang, laporan keuangan
- Owner — manage user per cabang, lihat semua laporan, set brand
- Superadmin — akses penuh sistem
- Pengunjung landing page — lihat info apotek untuk promosi

**Konteks teknis:**
- Development: XAMPP (Apache + PHP + MySQL) + phpMyAdmin
- Produksi: aaPanel (Linux, Apache/Nginx, PHP, MySQL)
- Database: 1 database MySQL pusat, tiap cabang terisolasi lewat `cabang_id`
- Printer: Bluetooth thermal receipt printer (BP ECO58D) — butuh local print helper
- Branding (nama, logo, warna, font): configurable dari admin panel

---

## 2. Arsitektur Sistem

### 2.1 Diagram Tingkat Tinggi

```
┌──────────────────────────────────────────────────────────────┐
│                 1 Database MySQL (pusat di aaPanel)          │
│                                                              │
│   cabang           user             obat                     │
│   ─────            ────            ────                      │
│   id, nama        id               id                        │
│   alamat          cabang_id       cabang_id                 │
│   telepon         nama            kode                       │
│   logo            username        nama                       │
│   warna_prim     password_hash   kategori                   │
│   warna_sec      role            harga_beli                 │
│   font           cabang_id       harga_jual                 │
│                  created_at      stok                        │
│                                  satuan                       │
│                                  expired_date                │
│                                  bpom_no                     │
│                                                              │
│   transaksi       transaksi_detail   setting_brand          │
│   ─────────       ──────────────     ────────────            │
│   id              id                 id                      │
│   cabang_id       transaksi_id      nama_apotek             │
│   user_id         obat_id           logo_path               │
│   no_transaksi    jumlah            primary_color           │
│   tanggal         harga_satuan      secondary_color         │
│   total           subtotal          font_family             │
│   metode_bayar                          │
│   created_at                             │
└──────────────────────────────────────────────────────────────┘
          │              │              │
          ▼              ▼              ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  Cabang A    │  │  Cabang B    │  │  Cabang C    │
│  Browser     │  │  Browser     │  │  Browser     │
│  + Printer   │  │  + Printer   │  │  + Printer   │
└──────────────┘  └──────────────┘  └──────────────┘
     │                 │                 │
     └─────────────────┼─────────────────┘
                       ▼
              ┌──────────────────────┐
              │  Local Print Helper   │
              │  (di perangkat kasir) │
              │  → Bluetooth Printer  │
              └──────────────────────┘
```

### 2.2 Prinsip Desain

- **1 database, banyak cabang:** Semua cabang connect ke 1 database pusat. Isolasi dilakukan di aplikasi level (queris scope ke `cabang_id` user yang login).
- **Role-based access:** Setiap request dicek role & cabang_id user sebelum memproses.
- **Configurable branding:** Warna, logo, font, nama apotek disimpan di `setting_brand`, dibaca oleh frontend setiap page load.
- **Local print helper:** Web app tidak akses Bluetooth printer langsung. Print job dikirim ke local service di perangkat kasir.

---

## 3. Struktur Berkas

```
apotek/
├── docs/
│   └── superpowers/
│       └── specs/
│           └── 2026-11-12-apotek-pos-design.md
├── app/
│   ├── config/
│   │   └── database.php        ← koneksi DB
│   ├── lib/
│   │   ├── db.php              ← wrapper PDO
│   │   ├── session.php         ← manage session & login
│   │   ├── rbac.php            ← cek role & cabang access
│   │   ├── print_helper.php    ← interface ke local print service
│   │   ├── format.php          ← format uang, tanggal, struk
│   │   └── validator.php       ← validasi input
│   ├── models/
│   │   ├── Cabang.php
│   │   ├── User.php
│   │   ├── Obat.php
│   │   ├── Transaksi.php
│   │   └── SettingBrand.php
│   ├── controllers/
│   │   ├── LoginController.php
│   │   ├── CabangController.php
│   │   ├── UserController.php
│   │   ├── ObatController.php
│   │   ├── TransaksiController.php
│   │   ├── LaporanController.php
│   │   ├── SettingController.php
│   │   └── LandingController.php
│   ├── views/
│   │   ├── layout/
│   │   │   ├── header.php      ← navbar, load brand setting
│   │   │   ├── footer.php
│   │   │   └── sidebar.php     ← menu based on role
│   │   ├── login.php
│   │   ├── pos/                ← antarmuka kasir
│   │   │   ├── index.php       ← halaman utama POS
│   │   │   ├── transaksi.php   ← input transaksi
│   │   │   └── cetak.php       ← preview & trigger cetak
│   │   ├── admin/             ← panel admin
│   │   │   ├── dashboard.php
│   │   │   ├── obat_list.php
│   │   │   ├── obat_form.php
│   │   │   ├── user_list.php
│   │   │   └── transaksi_list.php
│   │   ├── owner/             ← panel owner
│   │   │   ├── dashboard.php
│   │   │   ├── cabang_list.php
│   │   │   ├── user_manage.php
│   │   │   └── brand_setting.php
│   │   ├── keuangan/          ← panel keuangan
│   │   │   ├── dashboard.php
│   │   │   └── laporan_keu.php
│   │   └── landing/           ← halaman publik
│   │       └── index.php
│   └── public/
│       └── index.php           ← entry point (router)
├── assets/
│   ├── css/
│   │   └── style.css           ← main stylesheet
│   ├── js/
│   │   └── app.js              ← interaksi frontend
│   └── img/
│       └── logo/               ← logo default & upload
├── vendor/                      ← libs eksternal (kalau ada)
├── composer.json               ← dependency management (kalau perlu)
├── README.md
└── .htaccess                   ← URL rewrite
```

**Alur request:**
```
Browser → public/index.php → router → controller → model → view
```

---

## 4. Database Schema

### 4.1 Tabel `cabang`

```sql
CREATE TABLE cabang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  alamat TEXT,
  telepon VARCHAR(20),
  email VARCHAR(100),
  logo_path VARCHAR(255) DEFAULT NULL,
  warna_primer VARCHAR(20) DEFAULT '#1a73e8',
  warna_sekunder VARCHAR(20) DEFAULT '#ffffff',
  font_family VARCHAR(100) DEFAULT 'Inter',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4.2 Tabel `user`

```sql
CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cabang_id INT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('superadmin','owner','admin','kasir','keuangan') NOT NULL,
  foto_path VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE SET NULL
);
```

### 4.3 Tabel `obat`

```sql
CREATE TABLE obat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cabang_id INT NOT NULL,
  kode VARCHAR(20) NOT NULL,
  nama VARCHAR(200) NOT NULL,
  kategori VARCHAR(100),
  harga_beli DECIMAL(12,0) DEFAULT 0,
  harga_jual DECIMAL(12,0) NOT NULL,
  stok INT DEFAULT 0,
  stok_minimal INT DEFAULT 5,
  satuan VARCHAR(50) DEFAULT 'pcs',
 expired_date DATE DEFAULT NULL,
  bpom_no VARCHAR(50) DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
  UNIQUE KEY uk_cabang_kode (cabang_id, kode)
);
```

### 4.4 Tabel `transaksi`

```sql
CREATE TABLE transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cabang_id INT NOT NULL,
  user_id INT NOT NULL,
  no_transaksi VARCHAR(20) NOT NULL,
  tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(12,0) NOT NULL DEFAULT 0,
  metode_bayar ENUM('tunai','kartu','qris','transfer') DEFAULT 'tunai',
  catatan TEXT,
  is_closed TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE RESTRICT,
  UNIQUE KEY uk_cabang_no (cabang_id, no_transaksi)
);
```

### 4.5 Tabel `transaksi_detail`

```sql
CREATE TABLE transaksi_detail (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaksi_id INT NOT NULL,
  obat_id INT NOT NULL,
  jumlah INT NOT NULL,
  harga_satuan DECIMAL(12,0) NOT NULL,
  subtotal DECIMAL(12,0) NOT NULL,
  FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
  FOREIGN KEY (obat_id) REFERENCES obat(id) ON DELETE RESTRICT
);
```

### 4.6 Tabel `setting_brand` (Global)

```sql
CREATE TABLE setting_brand (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_apotek VARCHAR(200) NOT NULL DEFAULT 'Apotek',
  logo_path VARCHAR(255) DEFAULT NULL,
  primary_color VARCHAR(20) DEFAULT '#1a73e8',
  secondary_color VARCHAR(20) DEFAULT '#ffffff',
  font_family VARCHAR(100) DEFAULT 'Inter',
  tagline VARCHAR(255) DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4.7 Tabel `kategori_obat` (opsional, bisa langsung string)

```sql
CREATE TABLE kategori_obat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  is_active TINYINT(1) DEFAULT 1
);
```

---

## 5. RBAC — Role & Permissions

### 5.1 Definisi Role

| Role | Deskripsi |
|------|-----------|
| **superadmin** | Akses penuh sistem. Manage semua cabang, semua user, semua setelan. Tidak terikat cabang. |
| **owner** | Pemilik bisnis. Lihat semua cabang & laporan, manage user per cabang, set brand. Tidak bisa hapus config sistem. |
| **admin** | Kepala cabang. Hanya akses data milik cabang sendiri. CRUD obat, manage user di cabang sendiri, lihat laporan cabang. |
| **kasir** | Front-line cashier. Hanya input transaksi, cetak struk, cek stok. Tidak bisa lihat/edit data master. |
| **keuangan** | Fokus keuangan. Lihat transaksi & laporan keuangan semua cabang. Tidak bisa edit data atau input transaksi. |

### 5.2 Permission Matrix

| Fitur | superadmin | owner | admin | kasir | keuangan |
|-------|:----------:|:-----:|:-----:|:-----:|:--------:|
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| Logout | ✅ | ✅ | ✅ | ✅ | ✅ |
| Lho halaman dashboard | ✅ | ✅ | ✅ | ✅ (POS) | ✅ |
| Manage cabang (CRUD) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manage user (CRUD) | ✅ semua | ✅ per cabang | ❌ | ❌ | ❌ |
| Manage obat (CRUD) | ✅ semua | ✅ per cabang | ✅ per cabang | ❌ | ❌ |
| Input transaksi | ✅ | ✅ | ✅ | ✅ | ❌ |
| Cetak struk | ✅ | ✅ | ✅ | ✅ | ❌ |
| Lihat riwayat transaksi | ✅ semua | ✅ semua | ✅ cabang sendiri | ✅ cabang sendiri | ✅ semua |
| Laporan penjualan | ✅ semua | ✅ semua | ✅ cabang sendiri | ❌ | ✅ semua |
| Laporan keuangan | ✅ semua | ✅ semua | ❌ | ❌ | ✅ semua |
| Setting brand | ✅ | ✅ | ❌ | ❌ | ❌ |
| Setting cabang | ✅ | ✅ | ❌ | ❌ | ❌ |
| Export data | ✅ | ✅ | ❌ | ❌ | ✅ |

### 5.3 Implementasi RBAC (PHP Native)

**File:** `app/lib/rbac.php`

```php
function checkRole($requiredRoles) {
  // $requiredRoles = ['admin', 'superadmin']  → user boleh salah satu
  // $requiredRoles = ['superadmin']            → hanya superadmin
  $userRole = $_SESSION['user_role'] ?? null;
  if (!$userRole) return false;
  return in_array($userRole, $requiredRoles);
}

function checkCabangAccess($targetCabangId = null) {
  $userRole = $_SESSION['user_role'] ?? null;
  $userCabangId = $_SESSION['user_cabang_id'] ?? null;
  // Superadmin & owner: akses semua
  if (in_array($userRole, ['superadmin', 'owner'])) return true;
  // Role terikat cabang: harus cocok
  if (in_array($userRole, ['admin', 'kasir', 'keuangan'])) {
    if ($targetCabangId === null) return true; // ambil data milik cabang user
    return (int)$userCabangId === (int)$targetCabangId;
  }
  return false;
}

function requireLogin() {
  if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
  }
}
```

**Penggunaan di controller:**
```php
requireLogin();
if (!checkRole(['admin', 'owner', 'superadmin'])) {
  http_response_code(403);
  exit('Akses ditolak');
}
```

---

## 6. Fitur Breakdown

### 6.1 Landing Page (Publik)

Halaman promosi yang bisa diakses tanpa login.

**Section:**
1. **Header:** Logo + nama apotek + navigasi (Berdasarkan `setting_brand`)
2. **Hero:** Salam pembuka + value proposition apotek
3. **Layanan:** Daftar layanan apotek (konsultasi, vaksinasi, cek kesehatan, dll)
4. **Produk:** Kategori produk/obat yang tersedia
5. **Cabang:** Daftar 3 cabang + alamat, jam operasional, kontak
6. **Footer:** Copyright, media sosial, disclaimer

**Catatan:** Konten landing page bisa diatur dari admin/owner (setting brand minimal). Konten teks lain bisa di-hardcode dulu, nanti di-expand.

### 6.2 POS (Kasir)

Halaman utama kasir saat login sebagai kasir.

**Alur transaksi:**
1. Kasir buka halaman POS → tampil daftar obat (searchable, categorizable)
2. Scan barcode / search → tambah ke keranjang
3. Keranjang tampil di sidebar: daftar item, qty, harga, subtotal
4. Klik "Checkout" → konfirmasi total → pilih metode bayar → proses
5. TransaksiClosed → cetak struk (via local print helper) → kembalikan ke halaman POS

**Fitur tambahan:**
- Cek stok real-time
- Peringatan stok menipis (stok < stok_minimal)
- Kalkulasi otomatis (diskon, pajak kalau ada)
- Histori transaksi hari ini (miror)

### 6.3 Struk/Cetak

**Alur:**
1. Web app kirim data struk (JSON) ke local print helper via localhost HTTP
2. Local helper terima, format ke ESC/POS, kirim ke printer Bluetooth
3. Printer cetak struk

**Fallback development:** Print ke PDF / preview di layar dulu, nanti di-deploy baru connect ke printer.

### 6.4 Admin Panel

Halaman untuk role admin (kepala cabang).

**Menu:**
- Dashboard: ringkasan penjualan hari ini, stok menipis, transaksi terakhir
- Katalog Obat: CRUD obat, lihat stok, filter kategori
- Transaksi: lihat riwayat transaksi cabang, detail transaksi
- User (kalau owner/superadmin): manage user di cabang (admin tidak bisa)
- Setting (kalau owner/superadmin): set brand, warna, logo

### 6.5 Owner Panel

Halaman untuk role owner.

**Menu:**
- Dashboard All Cabang: performa tiap cabang, total pendapatan
- Manage Cabang: CRUD cabang, set logo & warna per cabang
- Manage User: CRUD user, assign cabang & role
- Brand Setting: set nama, logo, warna, font global
- Laporan: lihat semua laporan per cabang & gabungan

### 6.6 Keuangan Panel

Halaman untuk role keuangan.

**Menu:**
- Dashboard: ringkasan keuangan hari ini / minggu / bulan
- Laporan Penjualan: filter periode, export (CSV/Excel)
- Laporan Keuangan: pendapatan, metode bayar breakdown, laporan kas
- Transaksi: lihat semua transaksi semua cabang (read-only)

### 6.7 Superadmin

Hampir identik dengan owner, tapi dengan akses penuh ke config sistem (kalau ada).

---

## 7. UI/UX Direction

### 7.1 Prinsip Desain

- **Fungsionalitas utama:** POS harus cepat, mudah, dan minim error.
- **Kontras tinggi:** Teks harus mudah dibaca di lokasi yang mungkin terang (apotek).
- **Tombol besar:** Target tap minimum 44px, tombol aksi utama (Checkout, Cetak) harus prominent.
- **Responsive:** POS diakses dari laptop/All-in-One, tapi tetap harus usable dari tablet.
- **Warna brand:** Didrive oleh `setting_brand` → aplikasi menyesuaikan otomatis.

### 7.2 Landing Page Direction

- **Energi:** Sedang ( ENERGY 2 ) — profesional tapi welcoming
- **RHYTHM:** Varied ( RHYTHM 2 ) — section berbeda komposisi
- **MOTION:** Minimal ( MOTION 1 ) — hover states saja, nggak perlu scroll-reveal

**Karena branding di-set dari admin, desain landing page harus:**
- Menggunakan CSS variables yang di-set dari `setting_brand`
- Placeholder branding sampai admin upload logo & set warna

---

## 8. Print Strategy (Bluetooth)

### 8.1 Perangkat

**BP ECO58D** — Thermal receipt printer 58mm, Bluetooth.

### 8.2 Pendekatan

| Tahap | Cara |
|-------|------|
| **Development** | Preview struk di layar (HTML) + PDF. Nggak butuh printer fisik. |
| **Testing lokal** | Jika printer terhubung via USB/kabel, bisa test dengan local print helper. |
| **Production** | Install local print helper di perangkat kasir. Web app kirim print job ke localhost, helper forward ke Bluetooth printer. |

### 8.3 Format Struk (ESC/POS)

Struk minimal:
```
================================
  [NAMA APOTEK]
  [ALAMAT]
  [TELP]
  --------------------------------
  No. Transaksi: [NO]
  Tanggal: [TGL]
  Petugas: [NAMA KASIR]
  --------------------------------
  [NAMA PRODUK] x[QTY]  = [HARGA]
  ...
  --------------------------------
  TOTAL      : [TOTAL]
  BAYAR      : [BAYAR]
  KEMBALI    : [KEMBALI]
  --------------------------------
  Terima kasih!
================================
```

---

## 9. Tech Stack & Alasan

| Komponen | Pilihan | Alasan |
|----------|---------|--------|
| **Web server** | Apache (XAMPP / aaPanel) | Umum, mudah, support `.htaccess` URL rewrite |
| **Bahasa** | PHP native (tanpa framework) | User request, simpel, mudah di-maintain, tidak perlu kompleksitas framework |
| **Database** | MySQL 5.7+ | aaPanel support, phpMyAdmin familiar |
| **Session** | PHP native session + cookie | Simpel, cukup untuk app skala kecil |
| **CSS** | Vanilla CSS + CSS variables (branding) | Tidak berat, bisa di-custom dari admin |
| **JavaScript** | Vanilla JS (tidak framework) | Simpel, cukup untuk interaksi POS |
| **Print** | Local helper + ESC/POS | Wajib karena browser nggak akses Bluetooth langsung |
| **Barcode** | Scanner barcode = keyboard wedge (standar) | Scanner barcode umumnya terdeteksi sebagai keyboard, nggak butuh library khusus |

---

## 10. Rencana Implementasi Awal (MVP)

### Fase 1 — Fondasi
1. Setup XAMPP, buat database, buat tabel (SQL di atas)
2. Setup struktur berkas, koneksi DB, session management
3. Implementasi RBAC (login, logout, check role, check cabang)
4. Landing page pertama (dengan placeholder branding)

### Fase 2 — Katalog & User
5. CRUD obat (admin/owner/superadmin)
6. CRUD user (owner/superadmin)
7. Manage cabang (owner/superadmin)

### Fase 3 — Transaksi
8. Halaman POS (kasir): tampil obat, tambah ke keranjang
9. Checkout: hitung total, pilih metode bayar
10. Simpan transaksi ke database, kurangi stok obat
11. Preview struk (HTML)

### Fase 4 — Cetak & Laporan
12. Integrasi local print helper (kalau printer tersedia)
13. Laporan penjualan (admin & keuangan)
14. Laporan keuangan (keuangan)

### Fase 5 — Polish
15. Setting brand dari admin (logo, warna, font)
16. Antibot/pencegahan error di POS
17. Testing di semua role

---

## 11. Pertanyaan yang Belum Terselesaikan

- **Menu & alamat landing page:** Konten teks landing page bisa dari mana? (hardcode dulu, nanti di-setting)
- **Pajak/Diskon:** Apakah ada pajak atau diskon tertentu dihitung di transaksi?
- **Metode bayar:** Selain tunai, kartu, QRIS, transfer — ada metode lain?
- **File logo:** Format apa yang didukung? (PNG, JPG, SVG) — Nanti di-validasi saat upload.

---

*Spec ini siap untuk review. Setelah disetujui, akan dilanjutkan dengan menulis rencana implementasi detail.*
