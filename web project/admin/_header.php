<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Admin') . ' | ' . SITE_NAME) ?> Control Panel</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="admin">

<header class="site-header">
    <div class="container nav">
        <a class="logo" href="<?= url('admin/index.php') ?>">NO<span>CAP</span> <small>Admin</small></a>
        <nav class="open">
            <a href="<?= url('admin/index.php') ?>">Dashboard</a>
            <a href="<?= url('admin/products.php') ?>">Products</a>
            <a href="<?= url('admin/orders.php') ?>">Orders</a>
            <a href="<?= url('index.php') ?>" target="_blank">View Live Store &nearr;</a>
            <a href="<?= url('logout.php') ?>">Sign Out</a>
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

<main class="container section">
