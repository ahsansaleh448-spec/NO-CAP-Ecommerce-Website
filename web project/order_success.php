<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$st->execute([$id, current_user()['id']]);
$o = $st->fetch();

if (!$o) {
    redirect('orders.php');
}

$pageTitle = 'Order Confirmed';
require __DIR__ . '/includes/header.php';
?>

<div class="container auth center">
    <div class="tick">&#10003;</div>
    <h1>Thank You For Your Order!</h1>
    <p>Your order <b>#<?= (int)$o['id'] ?></b> has been received and is now being prepared for shipment.</p>
    
    <div class="success-card">
        <div class="sum"><span>Payment Method:</span><strong>Cash on Delivery (COD)</strong></div>
        <div class="sum"><span>Total Payable:</span><strong><?= money($o['total']) ?></strong></div>
        <div class="sum"><span>Delivery Contact:</span><span><?= e($o['phone']) ?></span></div>
        <div class="sum"><span>Delivery Address:</span><span><?= e($o['address']) ?>, <?= e($o['city']) ?></span></div>
    </div>

    <p class="muted" style="margin-top: 14px;">Our team will call or message your contact number prior to parcel delivery.</p>

    <div class="actions center" style="margin-top: 24px;">
        <a class="btn lg" href="<?= url('orders.php') ?>">View Order History</a>
        <a class="btn lg ghost" href="<?= url('products.php') ?>">Continue Shopping</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
