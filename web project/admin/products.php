<?php
$pageTitle = 'Products Catalog';
require __DIR__ . '/_header.php';

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
?>

<div class="head-row">
    <div>
        <h1>Products Catalog</h1>
        <p class="muted">Manage store inventory, update prices, and upload item photos.</p>
    </div>
    <a class="btn" href="<?= url('admin/product_form.php') ?>">+ Add New Product</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Available Stock</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td class="mini"><?= product_thumb($p) ?></td>
                <td>
                    <strong><?= e($p['name']) ?></strong>
                    <div class="muted" style="font-size:0.8rem;"><?= e(substr($p['description'] ?? '', 0, 60)) ?>...</div>
                </td>
                <td><span class="pill"><?= e($p['category']) ?></span></td>
                <td><b><?= money($p['price']) ?></b></td>
                <td>
                    <?php if ($p['stock'] < 1): ?>
                        <span class="stock-badge critical">Out of stock (0)</span>
                    <?php elseif ($p['stock'] <= 5): ?>
                        <span class="stock-badge warning"><?= (int)$p['stock'] ?> remaining</span>
                    <?php else: ?>
                        <span class="stock-badge healthy"><?= (int)$p['stock'] ?> in stock</span>
                    <?php endif; ?>
                </td>
                <td class="actions" style="justify-content: flex-end;">
                    <a class="btn sm ghost" href="<?= url('admin/product_form.php?id=' . $p['id']) ?>">Edit</a>
                    <a class="btn sm ghost" href="<?= url('product.php?id=' . $p['id']) ?>" target="_blank" title="View product page">&nearr;</a>
                    <form method="post" action="<?= url('admin/product_delete.php') ?>" onsubmit="return confirm('Are you sure you want to permanently delete \'<?= addslashes(e($p['name'])) ?>\'?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn sm danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?>
                <tr>
                    <td colspan="6" class="muted center-text" style="padding: 32px;">
                        No products found in the catalog. <a href="<?= url('admin/product_form.php') ?>">Add your first product</a>.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
