<?php
require_once __DIR__ . '/init.php';
$flash = get_flash();
$cartCount = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((isset($pageTitle) ? $pageTitle . ' | ' : '') . SITE_NAME . ' - Streetwear & Tech') ?></title>
    <meta name="description" content="NO CAP - Premium oversized hoodies, clean kicks, accessories, and tech. Real products, honest prices.">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="topbar">
    <span>Free delivery on orders over <?= money(FREE_SHIPPING_ABOVE) ?></span>
    <span class="dot">&bull;</span>
    <span>Cash on Delivery (COD) Available Nationwide</span>
</div>

<header class="site-header">
    <div class="container nav">
        <a class="logo" href="<?= url('index.php') ?>">NO<span>CAP</span></a>
        
        <form class="search" action="<?= url('products.php') ?>" method="get">
            <input type="search" name="q" placeholder="Search hoodies, kicks, tech..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
            <button type="submit" aria-label="Search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </form>

        <button class="burger" id="burger" aria-label="Toggle navigation menu" type="button">
            <span></span><span></span><span></span>
        </button>

        <nav id="menu">
            <a href="<?= url('index.php') ?>">Home</a>
            <a href="<?= url('products.php') ?>">Shop All</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('orders.php') ?>">My Orders</a>
                <?php if (is_admin()): ?>
                    <a class="admin-badge-link" href="<?= url('admin/index.php') ?>">Admin Panel</a>
                <?php endif; ?>
                <a href="<?= url('logout.php') ?>">Sign Out (<?= e(explode(' ', current_user()['name'])[0]) ?>)</a>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Sign In</a>
                <a class="btn sm" href="<?= url('register.php') ?>">Register</a>
            <?php endif; ?>
            <a class="cart-link" href="<?= url('cart.php') ?>">
                <span>Cart</span>
                <span class="pill" id="cart-counter"><?= $cartCount ?></span>
            </a>
        </nav>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container">
        <div class="alert <?= e($flash['type']) ?> fade-in">
            <?= e($flash['msg']) ?>
        </div>
    </div>
<?php endif; ?>

<main>
