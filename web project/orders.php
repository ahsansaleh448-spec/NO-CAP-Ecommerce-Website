<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$st = $pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');
$st->execute([current_user()['id']]);
$orders = $st->fetchAll();

$itemSt = $pdo->prepare('SELECT * FROM order_items WHERE order_id=?');
$pageTitle = 'My Orders';
require __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div class="head-row">
        <div>
            <h1>Order History</h1>
            <p class="muted">Track and review all previous purchases.</p>
        </div>
        <a class="btn sm ghost" href="<?= url('products.php') ?>">Continue Shopping</a>
    </div>

    <?php if (!$orders): ?>
        <div class="empty-state">
            <h3>No orders yet</h3>
            <p class="muted">You haven't placed any orders with us so far.</p>
            <a class="btn" href="<?= url('products.php') ?>">Browse Latest Drops &rarr;</a>
        </div>
    <?php endif; ?>

    <?php foreach ($orders as $o): 
        $itemSt->execute([$o['id']]); 
        $its = $itemSt->fetchAll(); 
    ?>
    <details class="order" open>
        <summary>
            <div>
                <b>Order #<?= (int)$o['id'] ?></b>
                <span class="muted" style="margin-left:8px;"><?= e(date('M d, Y &bull; h:i A', strtotime($o['created_at']))) ?></span>
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
                    <span>Shipping Delivery Fee</span>
                    <span><?= $o['shipping'] > 0 ? money($o['shipping']) : 'Free' ?></span>
                </div>
            </div>
            
            <div class="order-meta">
                <strong>Ship to:</strong> <?= e($o['full_name']) ?> &bull; <?= e($o['phone']) ?><br>
                <?= e($o['address']) ?>, <?= e($o['city']) ?> &bull; <em>Payment: <?= e($o['payment_method']) ?></em>
            </div>
        </div>
    </details>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
