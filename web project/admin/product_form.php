<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$p = [
    'name' => '',
    'category' => '',
    'description' => '',
    'price' => '',
    'stock' => '0',
    'image' => ''
];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) {
        set_flash('error', 'Product not found.');
        redirect('admin/products.php');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $p['name'] = trim($_POST['name'] ?? '');
    $p['category'] = trim($_POST['category'] ?? '');
    $p['description'] = trim($_POST['description'] ?? '');
    $p['price'] = $_POST['price'] ?? '';
    $p['stock'] = $_POST['stock'] ?? '0';

    if ($p['name'] === '') {
        $errors[] = 'Product name is required.';
    }
    if ($p['category'] === '') {
        $errors[] = 'Category name is required.';
    }
    if (!is_numeric($p['price']) || (float)$p['price'] < 0) {
        $errors[] = 'Please enter a valid price greater than or equal to zero.';
    }
    if (!ctype_digit((string)$p['stock'])) {
        $errors[] = 'Stock quantity must be a non-negative whole number.';
    }

    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed with error code: ' . $f['error'];
        } elseif ($f['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image size exceeds the maximum allowed limit of 2MB.';
        } elseif (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) || !@getimagesize($f['tmp_name'])) {
            $errors[] = 'Invalid image format. Only JPG, PNG, WEBP, or GIF images are allowed.';
        } else {
            $uploadsDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0775, true);
            }
            if (!is_writable($uploadsDir)) {
                $errors[] = "The uploads directory is not writable on your Linux system. Run: sudo chmod 775 " . realpath($uploadsDir);
            } else {
                $newImage = bin2hex(random_bytes(10)) . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], $uploadsDir . $newImage)) {
                    $errors[] = 'Failed to move uploaded image. Please check directory permissions.';
                    $newImage = null;
                }
            }
        }
    }

    if (!$errors) {
        $img = $newImage ?? ($p['image'] ?: null);
        if ($id) {
            $update = $pdo->prepare('UPDATE products SET name=?, category=?, description=?, price=?, stock=?, image=? WHERE id=?');
            $update->execute([$p['name'], $p['category'], $p['description'], $p['price'], (int)$p['stock'], $img, $id]);
            
            // Delete old physical image if replaced
            if ($newImage && !empty($_POST['old_image']) && !preg_match('#^https?://#i', $_POST['old_image'])) {
                @unlink(__DIR__ . '/../uploads/' . basename($_POST['old_image']));
            }
            set_flash('success', 'Product details were updated successfully.');
        } else {
            $insert = $pdo->prepare('INSERT INTO products (name, category, description, price, stock, image) VALUES (?,?,?,?,?,?)');
            $insert->execute([$p['name'], $p['category'], $p['description'], $p['price'], (int)$p['stock'], $img]);
            set_flash('success', 'New product was added to the catalog.');
        }
        redirect('admin/products.php');
    }
}

$cats = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category ASC')->fetchAll(PDO::FETCH_COLUMN);
$pageTitle = $id ? 'Edit Product' : 'Add New Product';
require __DIR__ . '/_header.php';
?>

<div class="head-row">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p class="muted">Fill in the product specifications and upload thumbnail images.</p>
    </div>
    <a class="btn sm ghost" href="<?= url('admin/products.php') ?>">&larr; Back to Catalog</a>
</div>

<?php foreach ($errors as $er): ?>
    <div class="alert error"><?= e($er) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="form wide">
    <?= csrf_field() ?>
    <input type="hidden" name="old_image" value="<?= e($p['image']) ?>">

    <label>Product Title / Name
        <input name="name" value="<?= e($p['name']) ?>" required placeholder="e.g. Heavyweight Cotton Hoodie">
    </label>

    <label>Product Category
        <input name="category" list="cats" value="<?= e($p['category']) ?>" required placeholder="e.g. Hoodies, Sneakers, Accessories, Tech">
        <datalist id="cats">
            <?php foreach ($cats as $c): ?>
                <option value="<?= e($c) ?>">
            <?php endforeach; ?>
        </datalist>
    </label>

    <div class="cols">
        <label>Unit Price (<?= e(trim(CURRENCY)) ?>)
            <input type="number" step="0.01" min="0" name="price" value="<?= e($p['price']) ?>" required placeholder="0.00">
        </label>
        <label>Available Inventory Stock
            <input type="number" min="0" name="stock" value="<?= e($p['stock']) ?>" required placeholder="0">
        </label>
    </div>

    <label>Detailed Description &amp; Specifications
        <textarea name="description" rows="5" placeholder="Fabric composition, fit style, color options, technical specs..."><?= e($p['description']) ?></textarea>
    </label>

    <label>Product Image (JPG, PNG, WEBP, GIF &bull; Max 2MB)
        <?php if (!empty($p['image'])): ?>
            <div style="display:flex; align-items:center; gap:12px; margin: 8px 0;">
                <span class="mini inline"><?= product_thumb(['id' => $id, 'name' => $p['name'] ?: 'X', 'image' => $p['image']]) ?></span>
                <span class="muted" style="font-size:0.85rem;">Current image: <?= e(basename($p['image'])) ?></span>
            </div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>

    <div class="actions" style="margin-top: 18px;">
        <button class="btn lg" type="submit"><?= $id ? 'Save Changes' : 'Create Product' ?></button>
        <a class="btn lg ghost" href="<?= url('admin/products.php') ?>">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
