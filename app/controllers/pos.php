<?php
/**
 * POS Controller — halaman Point of Sale untuk kasir.
 *
 * Role yang diizinkan: kasir, admin, owner, superadmin
 * (semua role bisa mengakses POS, tapi kasir adalah primary user)
 */
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/rbac.php';
require_once __DIR__ . '/../lib/helper.php';

require_login();
require_role(['kasir', 'admin', 'owner', 'superadmin']);

// Load obat untuk cabang user
$cabangId = $_SESSION['user_cabang_id'] ?? null;

// Jika user tidak punya cabang, lempar error
if ($cabangId === null && check_role(['admin'])) {
    http_response_code(403);
    echo 'Error: Admin harus memiliki cabang.';
    exit;
}

// Load obat
require_once __DIR__ . '/../models/ObatModel.php';
$obats = ObatModel::all($cabangId);
$lowStock = ObatModel::lowStock($cabangId);

// Cart dari session
$cart = $_SESSION['cart'] ?? [];

// Hitung total
$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += ($item['harga'] ?? 0) * ($item['qty'] ?? 0);
}

// Jika POST (tambah ke keranjang / hapus / update qty)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'addToCart') {
        $obatId = (int)($_POST['obat_id'] ?? 0);
        $qty    = max(1, (int)($_POST['qty'] ?? 1));

        if ($obatId > 0) {
            $obat = ObatModel::find($obatId);
            if ($obat && ($obat['cabang_id'] ?? 0) === ($cabangId ?? 0)) {
                if (!isset($cart[$obatId])) {
                    $cart[$obatId] = [
                        'qty'    => 0,
                        'harga'  => (int)$obat['harga_jual'],
                        'nama'   => $obat['nama'],
                        'kode'   => $obat['kode'],
                        'satuan' => $obat['satuan'],
                    ];
                }
                $cart[$obatId]['qty'] += $qty;
                $_SESSION['cart'] = $cart;
            }
        }
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }

    if ($postAction === 'removeFromCart') {
        $obatId = (int)($_POST['obat_id'] ?? 0);
        if (isset($cart[$obatId])) {
            unset($cart[$obatId]);
            $_SESSION['cart'] = $cart;
        }
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }

    if ($postAction === 'updateCart') {
        $obatId = (int)($_POST['obat_id'] ?? 0);
        $qty    = max(0, (int)($_POST['qty'] ?? 0));
        if (isset($cart[$obatId])) {
            if ($qty <= 0) {
                unset($cart[$obatId]);
            } else {
                $cart[$obatId]['qty'] = $qty;
            }
            $_SESSION['cart'] = $cart;
        }
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }

    if ($postAction === 'clearCart') {
        unset($_SESSION['cart']);
        header('Location: ' . BASE_URL . '/pos');
        exit;
    }

    if ($postAction === 'checkout') {
        // Redirect ke halaman checkout
        header('Location: ' . BASE_URL . '/pos/checkout.php');
        exit;
    }

    // Routing untuk action=checkout (kalau dibuka langsung)
    if (isset($_GET['action']) && $_GET['action'] === 'checkout') {
        require_once __DIR__ . '/../views/pos/checkout.php';
        exit;
    }
}

// Render
require_once __DIR__ . '/../views/pos/index.php';
