<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/products.php');
}

csrf_check();
$id = (int)($_POST['id'] ?? 0);

$st = $pdo->prepare('SELECT name, image FROM products WHERE id=?');
$st->execute([$id]);
$prod = $st->fetch();

if ($prod) {
    $img = $prod['image'] ?? null;
    $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    
    // Clean up local disk image if stored in uploads/
    if ($img && !preg_match('#^https?://#i', $img)) {
        $imgPath = __DIR__ . '/../uploads/' . basename($img);
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }
    set_flash('success', "Product '" . e($prod['name']) . "' was permanently removed.");
}

redirect('admin/products.php');
