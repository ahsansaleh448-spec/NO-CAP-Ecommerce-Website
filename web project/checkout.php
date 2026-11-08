<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$uid = current_user()['id'];

$st = $pdo->prepare('SELECT p.*, c.quantity FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?');
$st->execute([$uid]);
$items = $st->fetchAll();

if (!$items) {
    set_flash('error', 'Your shopping cart is currently empty.');
    redirect('cart.php');
}

$subtotal = 0;
foreach ($items as $i) {
    $subtotal += $i['price'] * $i['quantity'];
}
$shipping = shipping_for($subtotal);
$total = $subtotal + $shipping;

$u = $pdo->prepare('SELECT name, phone FROM users WHERE id=?');
$u->execute([$uid]);
$userProfile = $u->fetch();

$f = [
    'full_name' => $userProfile['name'] ?? '',
    'phone' => $userProfile['phone'] ?? '',
    'address' => '',
    'city' => ''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($f as $k => $_) {
        $f[$k] = trim($_POST[$k] ?? '');
    }

    if (strlen($f['full_name']) < 2) {
        $errors[] = 'Please provide your full legal name.';
    }
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $f['phone'])) {
        $errors[] = 'Please provide a valid contact phone number.';
    }
    if (strlen($f['address']) < 8) {
        $errors[] = 'Please enter a complete and accurate delivery street address.';
    }
    if ($f['city'] === '') {
        $errors[] = 'Please select or enter your city.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Lock and check stock availability
            $lock = $pdo->prepare('SELECT stock FROM products WHERE id=? FOR UPDATE');
            foreach ($items as $i) {
                $lock->execute([$i['id']]);
                $stock = (int)$lock->fetchColumn();
                if ($stock < $i['quantity']) {
                    throw new Exception("Stock unavailable for '{$i['name']}' (Only {$stock} unit(s) remaining).");
                }
            }

            // Create Order
            $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, full_name, phone, address, city, payment_method, shipping, total, status) VALUES (?,?,?,?,?,?,?,?,"Pending")');
            $orderStmt->execute([$uid, $f['full_name'], $f['phone'], $f['address'], $f['city'], 'Cash on Delivery', $shipping, $total]);
            $orderId = (int)$pdo->lastInsertId();

            // Insert Order Items and decrement stock
            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?,?,?,?,?)');
            $decStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id=?');

            foreach ($items as $i) {
                $itemStmt->execute([$orderId, $i['id'], $i['name'], $i['price'], $i['quantity']]);
                $decStmt->execute([$i['quantity'], $i['id']]);
            }

            // Clear user's cart
            $pdo->prepare('DELETE FROM cart WHERE user_id=?')->execute([$uid]);

            $pdo->commit();
            redirect('order_success.php?id=' . $orderId);
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $ex->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <h1>Complete Your Order</h1>
    
    <?php foreach ($errors as $er): ?>
        <div class="alert error"><?= e($er) ?></div>
    <?php endforeach; ?>

    <div class="cart-wrap">
        <form method="post" class="form grow">
            <?= csrf_field() ?>
            
            <div class="checkout-card">
                <h3>1. Delivery Address</h3>
                <label>Full Name
                    <input name="full_name" value="<?= e($f['full_name']) ?>" required placeholder="e.g. John Doe">
                </label>
                <div class="cols">
                    <label>Phone Number
                        <input name="phone" value="<?= e($f['phone']) ?>" required placeholder="e.g. +92 300 1234567">
                    </label>
                    <label>City
                        <input name="city" value="<?= e($f['city']) ?>" required placeholder="e.g. Lahore, Karachi, Islamabad">
                    </label>
                </div>
                <label>Complete Street Address
                    <textarea name="address" rows="3" required placeholder="House/Flat number, Street name, Area landmark..."><?= e($f['address']) ?></textarea>
                </label>
            </div>

            <div class="checkout-card">
                <h3>2. Payment Method</h3>
                <label class="radio-label">
                    <input type="radio" name="payment" value="cod" checked disabled>
                    <span><strong>Cash on Delivery (COD)</strong> &mdash; Pay in cash upon physical receipt of order.</span>
                </label>
            </div>

            <button class="btn lg block" type="submit">Confirm &amp; Place Order &rarr;</button>
        </form>

        <aside class="summary">
            <h3>Items in Order (<?= count($items) ?>)</h3>
            <div class="checkout-items-list">
                <?php foreach ($items as $i): ?>
                    <div class="sum">
                        <span><?= e($i['name']) ?> &times; <?= (int)$i['quantity'] ?></span>
                        <span><?= money($i['price'] * $i['quantity']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <div class="sum">
                <span>Subtotal</span>
                <span><?= money($subtotal) ?></span>
            </div>
            <div class="sum">
                <span>Shipping</span>
                <span><?= $shipping > 0 ? money($shipping) : '<strong style="color:var(--green)">Free</strong>' ?></span>
            </div>
            <div class="sum total">
                <span>Total Due</span>
                <span><?= money($total) ?></span>
            </div>
        </aside>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
