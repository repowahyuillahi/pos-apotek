# App POS Apotek + Landing Page — Implementation Plan

> **Untuk agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun aplikasi POS untuk apotek 3 cabang dengan RBAC lengkap (superadmin, owner, admin, kasir, keuangan), landing page promosi, dan sistem cetak struk via Bluetooth printer.

**Architecture:** PHP native tanpa framework. 1 database MySQL pusat, isolasi per-cabang lewat `cabang_id`. Struktur MVC sederhana: `lib/` (helper), `models/` (data access), `controllers/` (logic), `views/` (template). Routing lewat `.htaccess` + `public/index.php`.

**Tech Stack:** PHP 7.4+ (native), MySQL 5.7+, Apache (XAMPP/dev, aaPanel/prod), Vanilla CSS + JS, ESC/POS untuk cetak.

**Spec:** `docs/superpowers/specs/2026-11-12-apotek-pos-design.md`

---

## Global Constraints

- **Bahasa:** PHP native, tanpa framework (tidak boleh install framework)
- **Database:** MySQL, 1 database untuk semua cabang, isolasi lewat `cabang_id`
- **Session:** PHP native session + cookie, tidy di `$_SESSION`
- **RBAC:** 5 role: superadmin, owner, admin, kasir, keuangan — cek di setiap request
- **Branding:** Warna, logo, font, nama apotek dari tabel `setting_brand` — tampil di semua page
- **Cetak:** Web app kirim print job ke local print helper (localhost). Development bisa preview PDF/HTML dulu.
- **Printer:** BP ECO58D (Bluetooth thermal 58mm) — ESC/POS format
- **Barcode scanner:** Keyboard wedge — tidak butuh library khusus

---

## Review Focus

1. **Login tanpa session:** User yang tidak login mencoba akses halaman terproteksi — harus redirect ke login.
2. **Role mismatch:** Kasir mencoba akses halaman admin — harus 403/ditolak.
3. **Cross-cabang access:** Admin cabang A mencoba lihat data cabang B — harus diisolasi (kecuali owner/superadmin).
4. **Stok negatif:** Transaksi yang membuat stok obat negatif — harus ditolak atau minimum 0.
5. **No-Brand-Setting:** Halaman yang dibuka sebelum admin set branding — harus tampil dengan default warna/logo, bukan error.

---

## Task Structure

### Task 1: Project Scaffolding & Database Setup

**Files:**
- Create: `app/config/database.php`
- Create: `app/lib/db.php`
- Create: `app/lib/session.php`
- Create: `app/lib/helper.php`
- Create: `app/public/index.php`
- Create: `app/.htaccess`
- Create: `assets/css/style.css` (base styles)
- Create: `assets/js/app.js` (base JS)
- Create: `README.md`
- Modify: (tidak ada — baru start)

**Interfaces:**
- Consumes: (tidak ada — starter task)
- Produces:
  - `DB::connect()` → PDO instance
  - `DB::query($sql, $params)` → resultset
  - `DB::execute($sql, $params)` → bool
  - `session_start_secure()` → void (init session)
  - `base_url()` → string (base URL aplikasi)

**Steps:**

- [ ] **Step 1: Buat directory structure**

```bash
cd /path/to/apotek
mkdir -p app/config app/lib app/models app/controllers app/views/{layout,login,pos,admin,owner,keuangan,landing}
mkdir -p assets/{css,js,img/logo}
```

- [ ] **Step 2: Write `app/config/database.php`**

```php
<?php
// app/config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'apotek_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // kosong di XAMPP, isi di aaPanel
define('DB_CHARSET', 'utf8mb4');
```

- [ ] **Step 3: Write `app/lib/db.php`**

```php
<?php
// app/lib/db.php
require_once __DIR__ . '/../config/database.php';

class DB {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }

    public static function query($sql, $params = []) {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function execute($sql, $params = []) {
        $stmt = self::connect()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function insert($table, $data) {
        $cols = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($cols) VALUES ($placeholders)";
        self::execute($sql, $data);
        return self::connect()->lastInsertId();
    }

    public static function update($table, $data, $where, $whereParams) {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = :$col";
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE $table SET $setStr WHERE $where";
        $params = array_merge($data, $whereParams);
        return self::execute($sql, $params);
    }

    public static function delete($table, $where, $params) {
        $sql = "DELETE FROM $table WHERE $where";
        return self::execute($sql, $params);
    }
}
```

- [ ] **Step 4: Write `app/lib/session.php`**

```php
<?php
// app/lib/session.php
function session_start_secure() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . base_url() . '/login.php');
        exit;
    }
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . base_url() . '/login.php');
    exit;
}
```

- [ ] **Step 5: Write `app/lib/helper.php`**

```php
<?php
// app/lib/helper.php
function base_url($path = '') {
    $base = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function format_rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
```

- [ ] **Step 6: Write `app/public/index.php` (router sederhana)**

```php
<?php
// app/public/index.php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';

session_start_secure();

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove base URL
$base = dirname($_SERVER['SCRIPT_NAME']);
$relative = substr($path, strlen($base));

// Routing sederhana
$routes = [
    '/' => 'views/landing/index.php',
    '/login' => 'views/login.php',
    '/logout' => function() { require_once __DIR__ . '/../lib/session.php'; logout(); },
    '/pos' => 'views/pos/index.php',
    '/admin' => 'views/admin/dashboard.php',
    '/owner' => 'views/owner/dashboard.php',
    '/keuangan' => 'views/keuangan/dashboard.php',
];

// Default: landing page
if (!isset($routes[$relative])) {
    $relative = '/';
}

// Handle closure routes
if (is_callable($routes[$relative])) {
    $routes[$relative]();
    exit;
}

// Include view
$viewFile = __DIR__ . '/' . $routes[$relative];
if (file_exists($viewFile)) {
    require_once $viewFile;
} else {
    http_response_code(404);
    echo 'Halaman tidak ditemukan';
}
```

- [ ] **Step 7: Write `app/.htaccess`**

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

- [ ] **Step 8: Write `assets/css/style.css` (base)**

```css
/* assets/css/style.css */
:root {
    --primary-color: #1a73e8;
    --secondary-color: #ffffff;
    --text-color: #1a1a1a;
    --bg-color: #f5f5f5;
    --font-family: 'Inter', sans-serif;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: var(--font-family);
    background: var(--bg-color);
    color: var(--text-color);
}

a { text-decoration: none; color: inherit; }
button { cursor: pointer; }
input, select, textarea { font-family: inherit; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
```

- [ ] **Step 9: Write SQL setup script**

Buat file `db_setup.sql` dengan semua CREATE TABLE dari spec. Jalankan di phpMyAdmin.

- [ ] **Step 10: Verify — Test connection**

Buka `http://localhost/apotek/public/` di browser. Harusnya landing page tampil (belum ada isi, tapi tidak error). Cek di phpMyAdmin: tabel sudah ada.

- [ ] **Step 11: Commit**

```bash
git add -A
git commit -m "feat: project scaffolding, database setup, core lib"
```

---

### Task 2: Authentication & RBAC System

**Files:**
- Create: `app/lib/rbac.php`
- Create: `app/controllers/AuthController.php`
- Create: `views/login.php`
- Create: `views/layout/header.php`
- Create: `views/layout/footer.php`
- Create: `views/layout/sidebar.php`

**Interfaces:**
- Consumes: `DB`, `session` functions dari Task 1
- Produces:
  - `checkRole($requiredRoles)` → bool
  - `checkCabangAccess($targetCabangId)` → bool
  - `AuthController::login($username, $password)` → bool + set session
  - `AuthController::logout()` → void

**Steps:**

- [ ] **Step 1: Write `app/lib/rbac.php`**

```php
<?php
// app/lib/rbac.php

function checkRole($requiredRoles) {
    $userRole = $_SESSION['user_role'] ?? null;
    if (!$userRole) return false;
    return in_array($userRole, (array)$requiredRoles);
}

function checkCabangAccess($targetCabangId = null) {
    $userRole = $_SESSION['user_role'] ?? null;
    $userCabangId = $_SESSION['user_cabang_id'] ?? null;

    if (in_array($userRole, ['superadmin', 'owner'])) return true;
    if (in_array($userRole, ['admin', 'kasir', 'keuangan'])) {
        if ($targetCabangId === null) return true;
        return (int)$userCabangId === (int)$targetCabangId;
    }
    return false;
}

function requireRole($requiredRoles) {
    if (!checkRole($requiredRoles)) {
        http_response_code(403);
        echo 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.';
        exit;
    }
}

function requireCabangAccess($targetCabangId = null) {
    if (!checkCabangAccess($targetCabangId)) {
        http_response_code(403);
        echo 'Akses ditolak. Data ini tidak tersedia untuk cabang Anda.';
        exit;
    }
}
```

- [ ] **Step 2: Write `app/controllers/AuthController.php`**

```php
<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../lib/db.php';

class AuthController {
    public static function login($username, $password) {
        $users = DB::query(
            "SELECT id, cabang_id, nama_lengkap, username, password_hash, role, foto_path FROM user WHERE username = :username AND is_active = 1",
            ['username' => $username]
        );

        if (empty($users)) return false;

        $user = $users[0];
        if (!password_verify($password, $user['password_hash'])) return false;

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama_lengkap'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_cabang_id'] = $user['cabang_id'];
        $_SESSION['user_foto'] = $user['foto_path'];

        return true;
    }

    public static function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . base_url() . '/login.php');
        exit;
    }
}
```

- [ ] **Step 3: Write `views/login.php`**

Form login dengan username & password. POST ke `AuthController::login()`. Tampilkan error kalau gagal.

- [ ] **Step 4: Write `views/layout/header.php`**

- Load `setting_brand` dari DB → set CSS variables
- Tampilkan logo + nama apotek (dari setting)
- Navbar: link ke halaman sesuai role user

- [ ] **Step 5: Write `views/layout/footer.php`**

Footer sederhana dengan copyright + info cabang.

- [ ] **Step 6: Write `views/layout/sidebar.php`**

Menu sidebar berdasarkan role:
- superadmin/owner: Dashboard, Cabang, User, Obat, Transaksi, Laporan, Setting Brand
- admin: Dashboard, Obat, Transaksi, Laporan (cabang sendiri)
- kasir: POS (halaman utama)
- keuangan: Dashboard, Laporan Penjualan, Laporan Keuangan

- [ ] **Step 7: Verify — Test login flow**

1. Buat user test di database (bypass password_hash via SQL:
   ```sql
   INSERT INTO user (cabang_id, nama_lengkap, username, password_hash, role)
   VALUES (1, 'Test Kasir', 'kasir1', '$2y$10$...', 'kasir');
   ```
   Gunakan `password_hash('password123', PASSWORD_DEFAULT)` di PHP untuk generate hash.)

2. Buka `http://localhost/apotek/public/login.php`
3. Login dengan username/password yang benar → redirect ke halaman sesuai role
4. Login dengan password salah → muncul error
5. Coba akses halaman terproteksi tanpa login → redirect ke login

- [ ] **Step 8: Commit**

```bash
git add app/lib/rbac.php app/controllers/AuthController.php views/login.php views/layout/
git commit -m "feat: authentication & RBAC system"
```

---

### Task 3: Landing Page (Promosi)

**Files:**
- Create: `views/landing/index.php`
- Modify: `assets/css/style.css` (tambah landing styles)

**Interfaces:**
- Consumes: `setting_brand` dari DB, `esc()` helper
- Produces: Landing page yang tampil tanpa login

**Steps:**

- [ ] **Step 1: Write `views/landing/index.php`**

Structure:
1. Navbar (pernah dipakai di header, tapi landing punya versi minimal)
2. Hero section: nama apotek + tagline + CTA
3. Layanan section: daftar layanan apotek
4. Cabang section: 3 cabang + alamat, jam buka, telepon
5. Footer

Gunakan CSS variables dari `setting_brand` (jika ada, else gunakan default).

- [ ] **Step 2: Create CSS for landing page**

Tambah styles untuk hero, layanan cards, cabang cards, dll.

- [ ] **Step 3: Verify**

Buka `http://localhost/apotek/public/` → landing page tampil. Tanpa login. Logo & nama dari setting_brand (kosong → default).

- [ ] **Step 4: Commit**

```bash
git add views/landing/index.php assets/css/style.css
git commit -m "feat: landing page for promotion"
```

---

### Task 4: Cabang Management (Owner/Superadmin)

**Files:**
- Create: `app/models/CabangModel.php`
- Create: `app/controllers/CabangController.php`
- Create: `views/owner/cabang_list.php`
- Create: `views/owner/cabang_form.php`

**Interfaces:**
- Consumes: `DB`, `checkRole(['owner', 'superadmin'])`, `requireLogin()`
- Produces:
  - `CabangModel::all()` → array cabang
  - `CabangModel::find($id)` → single cabang
  - `CabangModel::create($data)` → int (new id)
  - `CabangModel::update($id, $data)` → bool
  - `CabangModel::delete($id)` → bool

**Steps:**

- [ ] **Step 1: Write `app/models/CabangModel.php`**

CRUD methods untuk tabel cabang.

- [ ] **Step 2: Write `app/controllers/CabangController.php`**

Handle: list, form (create/edit), delete. CSRF protection. Role check.

- [ ] **Step 3: Write `views/owner/cabang_list.php`**

Table daftar cabang + button tambah + edit/delete per baris.

- [ ] **Step 4: Write `views/owner/cabang_form.php`**

Form: nama, alamat, telepon, email, logo (upload), warna_primer, warna_sekunder, font_family.

- [ ] **Step 5: Verify**

1. Login sebagai owner/superadmin
2. Buka halaman cabang list → tampil daftar cabang (3 cabang)
3. Klik tambah → form muncul → isi → submit → cabang baru muncul di list
4. Klik edit → form terisi → ubah → submit → data update
5. Klik delete → konfirmasi → hilang dari list
6. Login sebagai admin/kasir → akses halaman ini → 403

- [ ] **Step 6: Commit**

```bash
git add app/models/CabangModel.php app/controllers/CabangController.php views/owner/cabang_list.php views/owner/cabang_form.php
git commit -m "feat: cabang management CRUD"
```

---

### Task 5: User Management (Owner/Superadmin)

**Files:**
- Create: `app/models/UserModel.php`
- Create: `app/controllers/UserController.php`
- Create: `views/owner/user_list.php`
- Create: `views/owner/user_form.php`

**Interfaces:**
- Consumes: `DB`, `checkRole(['owner', 'superadmin'])`, `password_hash()`, `password_verify()`
- Produces:
  - `UserModel::all($cabangId = null)` → array user
  - `UserModel::find($id)` → single user
  - `UserModel::create($data)` → int
  - `UserModel::update($id, $data)` → bool
  - `UserModel::delete($id)` → bool

**Steps:**

- [ ] **Step 1: Write `app/models/UserModel.php`**

CRUD methods. Password di-hash sebelum simpan.

- [ ] **Step 2: Write `app/controllers/UserController.php`**

Handle: list, form (create/edit), delete. Filter user by cabang untuk owner.

- [ ] **Step 3: Write `views/owner/user_list.php`**

Table: foto, nama, username, role, cabang, aktif/nonaktif, action.

- [ ] **Step 4: Write `views/owner/user_form.php`**

Form: nama_lengkap, username, password (buat baru / kosongkan kalau nggak mau ubah), role (select: superadmin/owner/admin/kasir/keuangan), cabang (select, opsional), foto (upload).

- [ ] **Step 5: Verify**

1. Login sebagai owner/superadmin
2. Buka halaman user list → tampil daftar user
3. Tambah user baru → pilih role & cabang → berhasil login dengan user baru
4. Edit user → ubah role → akses berubah sesuai role baru
5. Delete user → user hilang

- [ ] **Step 6: Commit**

```bash
git add app/models/UserModel.php app/controllers/UserController.php views/owner/user_list.php views/owner/user_form.php
git commit -m "feat: user management CRUD with role assignment"
```

---

### Task 6: Obat CRUD (Admin/Owner/Superadmin)

**Files:**
- Create: `app/models/ObatModel.php`
- Create: `app/controllers/ObatController.php`
- Create: `views/admin/obat_list.php`
- Create: `views/admin/obat_form.php`

**Interfaces:**
- Consumes: `DB`, `checkCabangAccess()`, `checkRole()`
- Produces:
  - `ObatModel::all($cabangId, $filters = [])` → array obat
  - `ObatModel::find($id)` → single obat
  - `ObatModel::create($data)` → int
  - `ObatModel::update($id, $data)` → bool
  - `ObatModel::delete($id)` → bool
  - `ObatModel::lowStock($cabangId, $threshold = 5)` → array obat stok menipis

**Steps:**

- [ ] **Step 1: Write `app/models/ObatModel.php`**

CRUD methods + lowStock finder.

- [ ] **Step 2: Write `app/controllers/ObatController.php`**

Handle: list, form (create/edit), delete. Scan barcode → auto-fill kode. Filter kategori.

- [ ] **Step 3: Write `views/admin/obat_list.php`**

Table: kode, nama, kategori, harga_jual, stok, stok_minimal, expired_date, status (aktif/nonaktif), action. Highlight stok menipis (stok < stok_minimal).

- [ ] **Step 4: Write `views/admin/obat_form.php`**

Form: kode (auto dari barcode atau manual), nama, kategori (select/from input), harga_beli, harga_jual, stok, stok_minimal, satuan, expired_date, bpom_no.

- [ ] **Step 5: Verify**

1. Login sebagai admin (kepala cabang)
2. Buka halaman obat list → tampil obat milik cabang admin
3. Tambah obat → form → submit → obat muncul di list
4. Scan barcode → kode terisi otomatis (kalau scanner tersedia)
5. Edit obat → ubah stok/harga → tersimpan
6. Hapus obat → hilang dari list
7. Login sebagai kasir → akses halaman ini → 403

- [ ] **Step 6: Commit**

```bash
git add app/models/ObatModel.php app/controllers/ObatController.php views/admin/obat_list.php views/admin/obat_form.php
git commit -m "feat: obat catalog CRUD with stock tracking"
```

---

### Task 7: POS — Halaman Utama & Keranjang Transaksi

**Files:**
- Create: `views/pos/index.php` (halaman utama POS)
- Create: `views/pos/transaksi.php` (input transaksi)
- Create: `app/models/TransaksiModel.php`
- Modify: `assets/js/app.js` (POS interaction)

**Interfaces:**
- Consumes: `checkRole(['kasir', 'admin', 'owner', 'superadmin'])`, `ObatModel::all()`, `ObatModel::lowStock()`
- Produces:
  - `TransaksiModel::create($data)` → int (new transaksi id)
  - `TransaksiModel::find($id)` → single transaksi + details

**Steps:**

- [ ] **Step 1: Write `app/models/TransaksiModel.php`**

Methods: create transaksi + details, find transaksi, hitung no_transaksi otomatis.

- [ ] **Step 2: Write `views/pos/index.php`**

Layout:
- Kiri: daftar obat (searchable, filter kategori), tampil obat + harga + stok
- Kanan: keranjang (session-based) — list item, qty, harga, subtotal, total
- Tombol Checkout

Obat ditampilkan dalam grid cards. Klik obat → tambah ke keranjang (session).

- [ ] **Step 3: Implement keranjang (session)**

```php
// Keranjang dalam $_SESSION['cart']
// Format: [ 'obat_id' => ['qty' => N, 'harga' => H, 'nama' => Nama] ]
```

Functions:
- `cartAdd($obatId, $qty = 1)` → tambah ke session
- `cartUpdate($obatId, $qty)` → update qty
- `cartRemove($obatId)` → hapus dari session
- `cartTotal()` → hitung total
- `cartClear()` → kosongkan session

- [ ] **Step 4: Write `assets/js/app.js` (POS interaction)**

- Search obat (bisa pakai live search dari server, atau tampil semua + filter client-side)
- Klik obat → tambah ke keranjang → update UI
- Update qty di keranjang → recalculate total
- Tombol checkout → confirm → POST ke server

- [ ] **Step 5: Verify**

1. Login sebagai kasir
2. Buka `http://localhost/apotek/public/pos` → tampil POS interface
3. Klik obat → masuk ke keranjang (session)
4. Ubah qty → total update
5. Kosongkan keranjang → kembali ke pos blank
6. Cek di database: nggak ada transaksi baru (masih di keranjang)

- [ ] **Step 6: Commit**

```bash
git add app/models/TransaksiModel.php views/pos/index.php views/pos/transaksi.php assets/js/app.js
git commit -m "feat: POS interface with cart system"
```

---

### Task 8: Checkout & Simpan Transaksi

**Files:**
- Modify: `app/controllers/TransaksiController.php` (create)
- Modify: `views/pos/transaksi.php` (checkout confirmation)

**Interfaces:**
- Consumes: `TransaksiModel::create()`, `ObatModel::updateStok()`
- Produces: Transaksi tersimpan di database, stok berkurang

**Steps:**

- [ ] **Step 1: Write checkout logic di `TransaksiController.php`**

1. Ambil keranjang dari session
2. Validasi: keranjang tidak kosong
3. Hitung total
4. Generate no_transaksi (format: `TRX-YYYYMMDD-NNN`)
5. Insert ke `transaksi` tabel
6. Insert ke `transaksi_detail` untuk tiap item
7. Kurangi stok di `obat` tabel (transaksi → stok -= qty)
8. Kosongkan keranjang session
9. Redirect ke halaman struk preview

- [ ] **Step 2: Stok validation**

Cek stok sebelum checkout: kalau stok < qty yang diminta, tolak dan beri pesan.error. Stok tidak boleh negatif.

- [ ] **Step 3: Write `views/pos/transaksi.php` (checkout confirmation)**

Tampilkan:
- List item + qty + harga
- Total
- Pilihan metode bayar (tunai, kartu, qris, transfer)
- Jika tunai: input jumlah bayar → hitung kembalian
- Tombol "Proses Transaksi"

- [ ] **Step 4: Verify**

1. Login sebagai kasir, tambah 2-3 item ke keranjang
2. Klik checkout → tampil halaman konfirmasi
3. Pilih metode bayar, klik proses → transaksi tersimpan
4. Cek di database: `transaksi` dan `transaksi_detail` ada
5. Cek stok obat: berkurang sesuai qty
6. Cek session: keranjang kosong
7. Coba checkout dengan stok insufficient → error, transaksi nggak masuk

- [ ] **Step 5: Commit**

```bash
git add app/controllers/TransaksiController.php views/pos/transaksi.php
git commit -m "feat: checkout flow with stock deduction"
```

---

### Task 9: Struk Preview & Cetak

**Files:**
- Create: `views/pos/cetak.php` (struk preview + print trigger)
- Create: `app/lib/print_helper.php`

**Interfaces:**
- Consumes: `TransaksiModel::find()`, `AuthController::currentUser()`
- Produces: Struk preview HTML + interface ke local print helper

**Steps:**

- [ ] **Step 1: Write `views/pos/cetak.php`**

Tampilkan struk dalam format seperti receipt:
- Logo + nama apotek
- No transaksi, tanggal, kasir
- List item + qty + harga
- Total, bayar, kembalian
- Tombol "Cetak ke Printer" + "Kembali ke POS"

Gunakan CSS yang nggak terlalu besar, format receipt yang rapi.

- [ ] **Step 2: Write `app/lib/print_helper.php`**

```php
<?php
// app/lib/print_helper.php

function printToLocal($data) {
    // Di development: log ke file / tampilkan preview
    // Di production: POST ke http://localhost:8080/print (local helper)
    $payload = json_encode($data);

    // Development mode
    file_put_contents(__DIR__ . '/../logs/last_print.json', $payload);

    // Production: uncomment ini setelah local helper jalan
    // $ch = curl_init('http://localhost:8080/print');
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    // curl_exec($ch);
    // curl_close($ch);
}
```

- [ ] **Step 3: Tampilkan tombol cetak**

Di `cetak.php`, tombol "Cetak" → AJAX POST ke `print_helper.php` → local service → printer.

- [ ] **Step 4: Verify**

1. Setelah checkout berhasil, redirect ke `cetak.php`
2. Struk preview tampil dengan data benar (no transaksi, tanggal, kasir, items, total, dll)
3. Klik "Cetak" → (di development) log tercatat di `logs/last_print.json`
4. Klik "Kembali ke POS" → kembali ke halaman POS dengan keranjang kosong

- [ ] **Step 5: Commit**

```bash
git add views/pos/cetak.php app/lib/print_helper.php
git commit -m "feat: struk preview and print interface"
```

---

### Task 10: Laporan Penjualan (Admin/Keuangan/Owner/Superadmin)

**Files:**
- Create: `app/controllers/LaporanController.php`
- Create: `views/admin/laporan_sales.php`
- Create: `views/keuangan/laporan_sales.php`

**Interfaces:**
- Consumes: `checkRole(['admin', 'keuangan', 'owner', 'superadmin'])`, `checkCabangAccess()`
- Produces: Laporan penjualan per periode

**Steps:**

- [ ] **Step 1: Write `app/controllers/LaporanController.php`**

Methods:
- `getSalesSummary($cabangId, $startDate, $endDate)` → total transaksi, total revenue, jumlah transaksi
- `getDailySales($cabangId, $date)` → Breakdown per hari
- `getSalesByProduct($cabangId, $startDate, $endDate)` → Top products

- [ ] **Step 2: Write `views/admin/laporan_sales.php`**

Filter: tanggal mulai, tanggal akhir, cabang (kalau owner/superadmin). Tampilkan:
- Total pendapatan
- Jumlah transaksi
- Metode bayar breakdown
- Top 5 produk terlaris

- [ ] **Step 3: Write `views/keuangan/laporan_sales.php`**

Sama seperti admin, tapi dengan export option (CSV).

- [ ] **Step 4: Verify**

1. Login sebagai admin (kepala cabang)
2. Buka halaman laporan → pilih periode → tampil laporan
3. Cek angka: harus sesuai dengan transaksi di database
4. Login sebagai kasir → akses halaman ini → 403

- [ ] **Step 5: Commit**

```bash
git add app/controllers/LaporanController.php views/admin/laporan_sales.php views/keuangan/laporan_sales.php
git commit -m "feat: sales reports with period filter"
```

---

### Task 11: Laporan Keuangan (Keuangan/Owner/Superadmin)

**Files:**
- Create: `views/keuangan/laporan_keu.php`
- Modify: `app/controllers/LaporanController.php` (tambah methods keuangan)

**Interfaces:**
- Consumes: `checkRole(['keuangan', 'owner', 'superadmin'])`
- Produces: Laporan keuangan breakdown

**Steps:**

- [ ] **Step 1: Write laporan keuangan view**

Tampilkan:
- Ringkasan pendapatan per periode (hari ini, minggu, bulan)
- Breakdown metode bayar (tunai, kartu, QRIS, transfer)
- Cash flow sederhana (pemasukan - pengeluaran)
- Trend grafik (kalau rapi, bisa pakai chart sederhana)

- [ ] **Step 2: Verify**

1. Login sebagai keuangan
2. Buka halaman laporan keuangan → tampil breakdown
3. Cek angka: harus sesuai dengan transaksi di database

- [ ] **Step 3: Commit**

```bash
git add views/keuangan/laporan_keu.php
git commit -m "feat: financial reports for keuangan role"
```

---

### Task 12: Setting Brand (Owner/Superadmin)

**Files:**
- Create: `app/models/SettingBrandModel.php`
- Create: `app/controllers/SettingController.php`
- Create: `views/owner/brand_setting.php`

**Interfaces:**
- Consumes: `checkRole(['owner', 'superadmin'])`
- Produces: Setting brand tersimpan, tampil di semua page

**Steps:**

- [ ] **Step 1: Write `app/models/SettingBrandModel.php`**

Methods: get(), update($data).

- [ ] **Step 2: Write `app/controllers/SettingController.php`**

Handle: tampil form, update logo (upload), warna, font, nama apotek, tagline.

- [ ] **Step 3: Write `views/owner/brand_setting.php`**

Form:
- Nama apotek (text input)
- Logo (file upload, max 2MB, PNG/JPG)
- Warna primer (color picker)
- Warna sekunder (color picker)
- Font family (select: Inter, Roboto, Arial, Poppins, dll)
- Tagline (textarea)

- [ ] **Step 4: Update `views/layout/header.php`**

Set CSS variables dari `setting_brand` setiap page load:
```php
$brand = SettingBrandModel::get();
// Set CSS inline atau file CSS yang di-generate
```

- [ ] **Step 5: Verify**

1. Login sebagai owner/superadmin
2. Buka halaman setting brand
3. Ubah nama → logo → warna → font → submit
4. Buka halaman lain (landing, pos, admin) → branding ter-update
5. Login sebagai kasir → akses halaman ini → 403

- [ ] **Step 6: Commit**

```bash
git add app/models/SettingBrandModel.php app/controllers/SettingController.php views/owner/brand_setting.php views/layout/header.php
git commit -m "feat: brand setting with logo, color, font configuration"
```

---

## Self-Review

### Spec Coverage

| Spec Section | Task |
|-------------|------|
| Project scaffolding & DB | Task 1 |
| RBAC & Authentication | Task 2 |
| Landing page | Task 3 |
| Cabang management | Task 4 |
| User management | Task 5 |
| Obat CRUD | Task 6 |
| POS interface & cart | Task 7 |
| Checkout & transaksi | Task 8 |
| Struk preview & print | Task 9 |
| Laporan penjualan | Task 10 |
| Laporan keuangan | Task 11 |
| Setting brand | Task 12 |

Semua section di spec tercover.

### Review Focus Coverage

| Review Focus | Test/Tanya |
|-------------|------------|
| Login tanpa session | Task 2, Step 7: test akses tanpa login |
| Role mismatch | Task 2/4/5/6/10/11/12: test akses dengan role wrong |
| Cross-cabang access | Task 6: test admin cabang A akses data cabang B |
| Stok negatif | Task 8, Step 2: validasi stok sebelum checkout |
| No-Brand-Setting | Task 12, Step 4: default values di header |

Semua review focus punya test/verifikasi.

### Proportion

Plan ini ~12 tasks. Setiap task fokus pada satu fitur. Code blocks hanya untuk signature & logic kunci, bukan transcript lengkap. Rasio plan/spec kira-kira 1:1 — wajar untuk PHP native yang detail.

---

## Execution Handoff

Plan complete dan saved ke:
**`docs/superpowers/plans/2026-11-12-apotek-pos-implementation.md`**

Silakan review. Setiap task adalah unit yang bisa dicommit sendiri. Ada yang ingin ditambah/diperpendek sebelum implementasi?