<?php
/**
 * Landing Page — halaman publik untuk promosi apotek.
 * Bisa diakses tanpa login.
 *
 * Konten:
 *   - Hero: nama apotek, tagline, CTA
 *   - Layanan: daftar layanan apotek
 *   - Cabang: 3 cabang + alamat & kontak
 *   - Footer
 *
 * Branding (nama, logo, warna, font) berasal dari setting_brand.
 * Konten teks layanan & cabang di-hardcode di sini, nanti bisa di-expand.
 */
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helper.php';

// Load brand
$brand = DB::fetchOne("SELECT * FROM setting_brand LIMIT 1") ?: [];
$brandName       = $brand['nama_apotek'] ?? 'Apotek Kami';
$brandLogo       = $brand['logo_path'] ?? null;
$brandPrimary    = $brand['primary_color'] ?? '#1a73e8';
$brandSecondary  = $brand['secondary_color'] ?? '#ffffff';
$brandFont       = $brand['font_family'] ?? 'Plus Jakarta Sans';
$brandTagline    = $brand['tagline'] ?? 'Pelayanan kesehatan terbaik untuk keluarga Anda';

$logoUrl = null;
if ($brandLogo) {
    $logoUrl = BASE_URL . '/assets/img/logo/' . $brandLogo;
}

// Load cabang yang aktif
$cabangs = DB::query("SELECT id, nama, alamat, telepon, email FROM cabang WHERE is_active = 1 ORDER BY id");

// Layanan (hardcode — nanti bisa dari DB / CMS)
$layanan = [
    [
        'icon'   => '💊',
        'title'  => 'Konsultasi Obat',
        'desc'   => 'Konsultasi dengan apoteker berpengalaman untuk memilih obat yang tepat.',
    ],
    [
        'icon'   => '💉',
        'title'  => 'Vaksinasi',
        'desc'   => 'Layanan vaksinasi lengkap untuk anak dan dewasa dengan standar keamanan.',
    ],
    [
        'icon'   => '🩺',
        'title'  => 'Pemeriksaan Kesehatan',
        'desc'   => 'Cek kesehatan rutin, tes darah, dan screening penyakit kronis.',
    ],
    [
        'icon'   => '📋',
        'title'  => 'Pengobatan Rakyat',
        'desc'   => 'Solusi herbal dan pengobatan tradisional yang teruji khasiatnya.',
    ],
    [
        'icon'   => '🏠',
        'title'  => 'Pengiriman Obat',
        'desc'   => 'Pesanan obat dikirim ke rumah dengan estimasi terpercaya.',
    ],
    [
        'icon'   => '💰',
        'title'  => 'Diskon & Promo',
        'desc'   => 'Dapatkan harga terbaik dan promo khusus untuk anggota setia.',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($brandName) ?> — <?= esc($brandTagline) ?></title>
    <meta name="description" content="<?= esc($brandTagline) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        :root {
            --primary-color: <?= $brandPrimary ?>;
            --secondary-color: <?= $brandSecondary ?>;
            --font-family: '<?= esc($brandFont) ?>', system-ui, -apple-system, sans-serif;
        }

        /* Landing page specific */
        .landing-hero {
            background: var(--bg-card);
            padding: 4rem 0 3rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        .landing-hero-badge {
            display: inline-block;
            padding: 0.25rem 1rem;
            border-radius: 9999px;
            background: rgba(26,115,232,0.1);
            color: var(--primary-color);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .landing-hero h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }
        .landing-hero .tagline {
            font-size: 1.125rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        .landing-hero-cta {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .landing-section {
            padding: 4rem 0;
        }
        .landing-section-title {
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .landing-section-sub {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .layanan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .layanan-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .layanan-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .layanan-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }
        .layanan-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .layanan-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .cabang-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }
        .cabang-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        .cabang-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .cabang-card-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .cabang-card-name {
            font-weight: 600;
            font-size: 1.0625rem;
        }
        .cabang-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .cabang-detail svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--text-muted);
        }
        .cabang-card-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.75rem;
        }
        .cabang-btn {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: #fff;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            transition: background 0.15s, color 0.15s;
        }
        .cabang-btn:hover {
            background: var(--bg-color);
            color: var(--text-color);
        }

        .landing-cta-section {
            background: var(--primary-color);
            color: #fff;
            padding: 3.5rem 0;
            text-align: center;
        }
        .landing-cta-section h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .landing-cta-section p {
            color: rgba(255,255,255,0.85);
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .footer-landing {
            background: #fff;
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
        }
        .footer-landing .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-landing p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .landing-hero { padding: 3rem 0 2rem; }
            .landing-section { padding: 3rem 0; }
            .landing-cta-section { padding: 2.5rem 0; }
        }
    </style>
</head>
<body>
    <!-- Navbar sederhana -->
    <nav class="navbar-landing" style="background:#fff; border-bottom:1px solid var(--border-color); position:sticky; top:0; z-index:100;">
        <div class="container" style="display:flex; align-items:center; justify-content:space-between; height:60px;">
            <a href="/" class="landing-nav-brand" style="display:flex; align-items:center; gap:0.5rem; font-weight:600; color:var(--primary-color); font-size:1.125rem;">
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="" style="height:32px; width:auto;">
                <?php endif; ?>
                <?= esc($brandName) ?>
            </a>
            <div style="display:flex; gap:0.75rem; align-items:center;">
                <a href="#layanan" style="font-size:0.9rem; color:var(--text-muted);">Layanan</a>
                <a href="#cabang" style="font-size:0.9rem; color:var(--text-muted);">Cabang</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-sm">Masuk</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="landing-hero">
        <div class="container">
            <div class="landing-hero-badge">Selamat Datang di <?= esc($brandName) ?></div>
            <h1><?= esc($brandName) ?></h1>
            <p class="tagline"><?= esc($brandTagline) ?></p>
            <div class="landing-hero-cta">
                <a href="#layanan" class="btn btn-primary btn-lg">Lihat Layanan</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary btn-lg" style="border:1px solid var(--primary-color); color:var(--primary-color);">Masuk Sistem</a>
            </div>
        </div>
    </section>

    <!-- Layanan -->
    <section class="landing-section" id="layanan">
        <div class="container">
            <h2 class="landing-section-title">Layanan Kami</h2>
            <p class="landing-section-sub">Solusi kesehatan lengkap untuk keluarga Anda, dengan pelayanan profesional dan harga terjangkau.</p>
            <div class="layanan-grid">
                <?php foreach ($layanan as $lay): ?>
                    <div class="layanan-card">
                        <div class="layanan-icon"><?= esc($lay['icon']) ?></div>
                        <h3 class="layanan-title"><?= esc($lay['title']) ?></h3>
                        <p class="layanan-desc"><?= esc($lay['desc']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Cabang -->
    <section class="landing-section" id="cabang" style="background:var(--bg-color);">
        <div class="container">
            <h2 class="landing-section-title">Lokasi Cabang</h2>
            <p class="landing-section-sub">Kami hadir di 3 lokasi untuk melayani Anda. Kunjungi cabang terdekat.</p>
            <div class="cabang-grid">
                <?php foreach ($cabangs as $i => $cab): ?>
                    <div class="cabang-card">
                        <div class="cabang-card-header">
                            <div class="cabang-card-number"><?= esc($i + 1) ?></div>
                            <div class="cabang-card-name"><?= esc($cab['nama']) ?></div>
                        </div>
                        <?php if ($cab['alamat']): ?>
                            <div class="cabang-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span><?= esc($cab['alamat']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($cab['telepon']): ?>
                            <div class="cabang-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.902.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.908.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                <span><?= esc($cab['telepon']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="cabang-card-footer">
                            <a href="tel:<?= esc($cab['telepon'] ?? '') ?>" class="cabang-btn">Hubungi</a>
                            <a href="#" class="cabang-btn">Lihat Arah</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="landing-cta-section">
        <div class="container">
            <h2>Butuh konsultasi obat?</h2>
            <p>Hubungi kami atau kunjungi cabang terdekat. Tim apoteker kami siap membantu.</p>
            <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
                <a href="#layanan" class="btn btn-lg" style="background:#fff; color:var(--primary-color); border:none;">Lihat Layanan</a>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-lg" style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3);">Masuk Sistem POS</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-landing">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= esc($brandName) ?>. Hak cipta dilindungi undang-undang.</p>
            <p style="font-size:0.8rem;">Dibuat dengan ❤️ untuk pelayanan kesehatan Indonesia.</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
