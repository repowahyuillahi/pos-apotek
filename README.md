# App POS Apotek

Aplikasi Point-of-Sale untuk apotek dengan 3 cabang, dibangun dengan PHP native.

## Stack

- **PHP** native (tanpa framework)
- **MySQL** (1 database pusat, isolasi per-cabang via `cabang_id`)
- **Apache** (XAMPP untuk development, aaPanel untuk production)
- **CSS/JS** vanilla

## Struktur

```
apotek/
├── app/
│   ├── config/          # database.php
│   ├── lib/             # db.php, session.php, helper.php, rbac.php
│   ├── models/          # model per entitas
│   ├── controllers/     # logic per fitur
│   └── views/           # template per role & halaman
├── assets/
│   ├── css/             # style.css
│   └── js/              # app.js
├── public/
│   └── index.php        # front controller / router
├── docs/
│   └── superpowers/
│       ├── specs/       # design spec
│       └── plans/       # implementation plan
└── README.md
```

## Setup Development (XAMPP)

1. **Install XAMPP** dan jalankan Apache + MySQL.
2. **Buat database** di phpMyAdmin:
   - Buka `http://localhost/phpmyadmin`
   - Buat database `apotek_db` (UTF8mb4)
   - Import `db_setup.sql` (lihat di bawah)
3. **Place project** di `htdocs`:
   - Copy folder `apotek` ke `C:\xampp\htdocs\`
4. **Konfigurasi** `app/config/database.php` (default sudah XAMPP).
5. **Buka** `http://localhost/apotek/` di browser.

## Database Setup

File SQL: `db_setup.sql` (dibuat terpisah, import lewat phpMyAdmin).

Tabel yang dibuat:
- `cabang` — daftar 3 cabang
- `user` — user dengan role (superadmin, owner, admin, kasir, keuangan)
- `obat` — katalog obat per cabang
- `transaksi` — header transaksi
- `transaksi_detail` — item transaksi
- `setting_brand` — branding global (nama, logo, warna, font)

## Role & Akses

| Role | Akses |
|------|-------|
| superadmin | Full system access |
| owner | All cabang, all reports, manage user & brand |
| admin | Cabang sendiri: obat CRUD, transaksi view, laporan cabang |
| kasir | Input transaksi, cetak struk, cek stok |
| keuangan | Read-only transaksi semua, laporan keuangan |

## Printer (BP ECO58D — Bluetooth)

Web app tidak akses Bluetooth printer langsung. Cetak dikirim ke **local print helper** yang berjalan di perangkat kasir.

- Development: preview struk di layar + log ke file.
- Production: install local helper → web app POST ke localhost → helper kirim ESC/POS ke printer.

## Branding

Nama apotek, logo, warna primer/sekunder, dan font disimpan di tabel `setting_brand`. Di-load di header setiap page → CSS variables di-update secara dinamis.

## License

Internal use.
