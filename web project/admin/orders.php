<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $oid = (int)($_POST['id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $statuses, true)) {
        $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$newStatus, $oid]);
        set_flash('success', "Order #$oid status was successfully updated to '{$newStatus}'.");
    }
    redirect('admin/orders.php');
}

$orders = $pdo->query('SELECT o.*, u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC')->fetchAll();
$itemSt = $pdo->prepare('SELECT * FROM order_items WHERE order_id=?');
$pageTitle = 'Orders Management';
require __DIR__ . '/_header.php';
?>

<div class="head-row">
    <div>
        <h1>Orders Management</h1>
        <p class="muted">Review incoming purchases and update fulfillment status.</p>
    </div>
    <div>
        <span class="pill" style="font-size: 0.95rem; padding: 6px 14px;"><?= count($orders) ?> Total Orders</span>
    </div>
</div>

<?php if (!$orders): ?>
    <div class="empty-state">
        <h3>No orders received yet</h3>
        <p class="muted">Orders submitted by customers will show up here.</p>
    </div>
<?php endif; ?>

<?php foreach ($orders as $o): 
    $itemSt->execute([$o['id']]); 
    $its = $itemSt->fetchAll(); 
?>
<details class="order" open>
    <summary>
        <div style="display:flex; align-items:center; gap:12px;">
            <b>Order #<?= (int)$o['id'] ?></b>
            <span><?= e($o['full_name']) ?></span>
            <span class="muted"><?= e(date('M d, Y &bull; h:i A', strtotime($o['created_at']))) ?></span>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="status s-<?= e(strtolower($o['status'])) ?>"><?= e($o['status']) ?></span>
            <b><?= money($o['total']) ?></b>
        </div>
    </summary>
    
    <div class="order-body">
        <div class="order-items-table">
            <?php foreach ($its as $i): ?>
                <div class="sum">
                    <span><?= e($i['product_name']) ?> &times; <?= (int)$i['quantity'] ?></span>
                    <span><?= money($i['price'] * $i['quantity']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="sum">
                <span>Shipping Delivery Fee:</span>
                <span><?= $o['shipping'] > 0 ? money($o['shipping']) : 'Free' ?></span>
            </div>
        </div>

        <div class="order-meta" style="margin: 12px 0;">
            <strong>Customer:</strong> <?= e($o['full_name']) ?> &bull; <strong>Phone:</strong> <?= e($o['phone']) ?> <?= !empty($o['email']) ? '&bull; <strong>Email:</strong> ' . e($o['email']) : '' ?><br>
            <strong>Shipping Address:</strong> <?= e($o['address']) ?>, <?= e($o['city']) ?><br>
            <strong>Payment Method:</strong> <?= e($o['payment_method']) ?>
        </div>

        <form method="post" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                Update Fulfillment Status:
                <select name="status">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s) ?>" <?= $s === $o['status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn sm" type="submit">Save Status</button>
        </form>
    </div>
</details>
<?php endforeach; ?>

<?php require __DIR__ . '/_footer.php'; ?>
