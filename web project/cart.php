<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);

    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to add items to your shopping cart.');
        redirect('login.php?next=' . urlencode($pid ? 'product.php?id=' . $pid : 'cart.php'));
    }
    $uid = current_user()['id'];

    $ps = $pdo->prepare('SELECT id, name, stock, price FROM products WHERE id=?');
    $ps->execute([$pid]);
    $prod = $ps->fetch();

    if ($action === 'add' && $prod) {
        if ($prod['stock'] < 1) {
            set_flash('error', 'Sorry, ' . $prod['name'] . ' is currently out of stock.');
        } else {
            $cur = $pdo->prepare('SELECT quantity FROM cart WHERE user_id=? AND product_id=?');
            $cur->execute([$uid, $pid]);
            $have = (int)$cur->fetchColumn();
            $new = min($have + max(1, $qty), (int)$prod['stock']);
            
            $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)')
                ->execute([$uid, $pid, $new]);
                
            set_flash('success', $prod['name'] . ' was added to your cart.');
        }
    } elseif ($action === 'update' && $prod) {
        if ($qty < 1) {
            $pdo->prepare('DELETE FROM cart WHERE user_id=? AND product_id=?')->execute([$uid, $pid]);
            set_flash('success', 'Item removed from your cart.');
        } else {
            $newQty = min($qty, (int)$prod['stock']);
            $pdo->prepare('UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?')
                ->execute([$newQty, $uid, $pid]);
            set_flash('success', 'Cart quantities updated.');
        }
    } elseif ($action === 'remove') {
        $pdo->prepare('DELETE FROM cart WHERE user_id=? AND product_id=?')->execute([$uid, $pid]);
        set_flash('success', 'Item removed from your cart.');
    }
    redirect('cart.php');
}

require_login();
$st = $pdo->prepare('SELECT p.*, c.quantity FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=? ORDER BY c.id DESC');
$st->execute([current_user()['id']]);
$items = $st->fetchAll();

$subtotal = 0;
foreach ($items as $i) {
    $subtotal += $i['price'] * $i['quantity'];
}
$shipping = shipping_for($subtotal);

$pageTitle = 'Shopping Cart';
require __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <h1>Your Shopping Cart</h1>
    
    <?php if (!$items): ?>
        <div class="empty-state">
            <h3>Your cart is empty</h3>
            <p class="muted">Looks like you haven't added any gear yet. Discover our fresh drops!</p>
            <a class="btn lg" href="<?= url('products.php') ?>">Start Shopping &rarr;</a>
        </div>
    <?php else: ?>
    <div class="cart-wrap">
        <div class="cart-list">
            <?php foreach ($items as $i): ?>
            <div class="cart-row">
                <a class="mini" href="<?= url('product.php?id=' . $i['id']) ?>">
                    <?= product_thumb($i) ?>
                </a>
                <div class="grow">
                    <span class="cat"><?= e($i['category']) ?></span>
                    <a href="<?= url('product.php?id=' . $i['id']) ?>">
                        <b><?= e($i['name']) ?></b>
                    </a>
                    <div class="muted"><?= money($i['price']) ?> each</div>
                    <?php if ($i['quantity'] > $i['stock']): ?>
                        <div class="warn">Only <?= (int)$i['stock'] ?> items remaining in stock. Please adjust quantity.</div>
                    <?php endif; ?>
                </div>

                <form method="post" class="qty">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= (int)$i['id'] ?>">
                    <input type="number" name="quantity" value="<?= (int)$i['quantity'] ?>" min="0" max="<?= (int)$i['stock'] ?>" aria-label="Quantity">
                    <button class="btn sm ghost" type="submit">Update</button>
                </form>

                <div class="line"><?= money($i['price'] * $i['quantity']) ?></div>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= (int)$i['id'] ?>">
                    <button class="x" type="submit" aria-label="Remove item" title="Remove">&times;</button>
                </form>
            </div>
            <?php endforeach; ?>
            
            <div style="padding-top: 16px;">
                <a class="link-arrow" href="<?= url('products.php') ?>">&larr; Continue Shopping</a>
            </div>
        </div>

        <aside class="summary">
            <h3>Order Summary</h3>
            <div class="sum">
                <span>Subtotal</span>
                <span><?= money($subtotal) ?></span>
            </div>
            <div class="sum">
                <span>Standard Delivery</span>
                <span><?= $shipping > 0 ? money($shipping) : '<strong style="color:var(--green)">FREE</strong>' ?></span>
            </div>
            <?php if ($shipping > 0): ?>
                <div class="free-shipping-tip">
                    Add <?= money(FREE_SHIPPING_ABOVE - $subtotal) ?> more to get <strong>Free Delivery</strong>!
                </div>
            <?php endif; ?>
            <div class="sum total">
                <span>Estimated Total</span>
                <span><?= money($subtotal + $shipping) ?></span>
            </div>
            <a class="btn lg block" href="<?= url('checkout.php') ?>">Proceed to Checkout &rarr;</a>
        </aside>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
