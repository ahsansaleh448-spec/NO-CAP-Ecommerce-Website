<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/_header.php';

$stats = [
    'Catalog Products' => $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Total Orders' => $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'Registered Customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    'Total Revenue' => money($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status<>'Cancelled'")->fetchColumn()),
];

$recent = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
$low = $pdo->query('SELECT id, name, category, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 8')->fetchAll();
?>

<div class="head-row">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Store performance metrics and inventory status.</p>
    </div>
    <div>
        <a class="btn sm" href="<?= url('admin/product_form.php') ?>">+ Add New Product</a>
    </div>
</div>

<div class="stats">
    <?php foreach ($stats as $k => $v): ?>
        <div class="stat">
            <small><?= e($k) ?></small>
            <b><?= e($v) ?></b>
        </div>
    <?php endforeach; ?>
</div>

<div class="two">
    <div class="card-section">
        <div class="head-row">
            <h2>Recent Orders</h2>
            <a class="link-arrow" href="<?= url('admin/orders.php') ?>">View all &rarr;</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $o): ?>
                    <tr>
                        <td><b>#<?= (int)$o['id'] ?></b></td>
                        <td><?= e($o['full_name']) ?></td>
                        <td><?= money($o['total']) ?></td>
                        <td><span class="status s-<?= e(strtolower($o['status'])) ?>"><?= e($o['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?>
                        <tr><td colspan="4" class="muted center-text" style="padding:24px;">No orders have been placed yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-section">
        <div class="head-row">
            <h2>Low Stock Alerts</h2>
            <a class="link-arrow" href="<?= url('admin/products.php') ?>">Inventory &rarr;</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Remaining Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low as $l): ?>
                    <tr>
                        <td><strong><?= e($l['name']) ?></strong></td>
                        <td><span class="muted"><?= e($l['category']) ?></span></td>
                        <td>
                            <span class="stock-badge <?= (int)$l['stock'] === 0 ? 'critical' : 'warning' ?>">
                                <?= (int)$l['stock'] === 0 ? 'Sold Out (0)' : (int)$l['stock'] . ' left' ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn sm ghost" href="<?= url('admin/product_form.php?id=' . $l['id']) ?>">Restock</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$low): ?>
                        <tr><td colspan="4" class="muted center-text" style="padding:24px;">All inventory levels are healthy! No low-stock items.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
