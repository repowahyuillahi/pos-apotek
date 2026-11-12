<?php
/**
 * Halaman login.
 * Tanpa require_login — ini halaman masuk.
 *
 * Jika sudah login, redirect ke dashboard sesuai role.
 * Jika ada flash message, tampilkan.
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../controllers/AuthController.php';

session_start_secure();

// Jika sudah login, redirect ke halaman masing-masing
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . AuthController::redirectAfterLogin());
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        if (AuthController::login($username, $password)) {
            header('Location: ' . BASE_URL . AuthController::redirectAfterLogin());
            exit;
        }
        $error = 'Username atau password salah.';
    }
}

$flash = AuthController::getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Apotek</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-color);
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .login-logo .brand-tagline {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        .login-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .login-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .login-error {
            background: rgba(229,57,53,0.1);
            color: var(--accent-warn);
            border: 1px solid rgba(229,57,53,0.2);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <div class="brand-name">APOTEK</div>
                <div class="brand-tagline">POS System</div>
            </div>
            <h1 class="login-title">Selamat Datang</h1>
            <p class="login-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

            <?php if ($error): ?>
                <div class="login-error"><?= esc($error) ?></div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="alert alert-<?= esc($flash['type']) ?>">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/login.php" novalidate>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control <?= $error ? 'is-invalid' : '' ?>"
                        autocomplete="username"
                        autofocus
                        required
                    >
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control <?= $error ? 'is-invalid' : '' ?>"
                        autocomplete="current-password"
                        required
                    >
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="login-footer">
                Apotek POS &mdash; Hak cipta dilindungi.
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
