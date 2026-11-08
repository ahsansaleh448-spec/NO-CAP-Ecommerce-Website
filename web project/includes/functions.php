<?php
/**
 * Core Helper Functions
 */

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function money($amount) {
    return CURRENCY . number_format((float)$amount, 2);
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function get_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Invalid or expired security token. Please refresh the page and try again.');
    }
}

function is_logged_in() {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return is_logged_in() && (($_SESSION['user']['role'] ?? '') === 'admin');
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to access this page.');
        $currentUri = ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
        redirect('login.php?next=' . urlencode($currentUri));
    }
}

function require_admin() {
    if (!is_admin()) {
        set_flash('error', 'Administrator privileges required.');
        redirect('login.php');
    }
}

function safe_next($next) {
    $next = ltrim((string)$next, '/');
    if (BASE_URL !== '' && strpos($next, ltrim(BASE_URL, '/') . '/') === 0) {
        $next = substr($next, strlen(ltrim(BASE_URL, '/')) + 1);
    }
    if ($next === '' || preg_match('#(^[a-z]+:)|//|\.\.#i', $next) || !preg_match('#^[A-Za-z0-9_\-/\.\?=&%]+$#', $next)) {
        return 'index.php';
    }
    return $next;
}

function cart_count() {
    global $pdo;
    if (!is_logged_in()) return 0;
    try {
        $st = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?');
        $st->execute([current_user()['id']]);
        return (int)$st->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function shipping_for($subtotal) {
    return ($subtotal <= 0 || $subtotal >= FREE_SHIPPING_ABOVE) ? 0.0 : SHIPPING_FEE;
}

function product_thumb($product) {
    if (!empty($product['image'])) {
        $src = preg_match('#^https?://#i', $product['image']) ? $product['image'] : url('uploads/' . $product['image']);
        return '<img src="' . e($src) . '" alt="' . e($product['name']) . '" loading="lazy">';
    }
    $hue = ((int)($product['id'] ?? 1) * 47) % 360;
    $initials = strtoupper(substr($product['name'] ?? 'NP', 0, 2));
    return '<div class="ph" style="--h:' . $hue . '"><span>' . e($initials) . '</span></div>';
}

function product_card($product) {
    $isOutOfStock = ($product['stock'] < 1);
    ob_start(); ?>
    <div class="card <?= $isOutOfStock ? 'is-out-of-stock' : '' ?>">
        <a class="thumb" href="<?= url('product.php?id=' . $product['id']) ?>">
            <?= product_thumb($product) ?>
            <?php if ($isOutOfStock): ?>
                <em class="badge out">Sold out</em>
            <?php elseif ((int)$product['stock'] <= 5): ?>
                <em class="badge low">Only <?= (int)$product['stock'] ?> left</em>
            <?php endif; ?>
        </a>
        <div class="info">
            <span class="cat"><?= e($product['category']) ?></span>
            <h3><a href="<?= url('product.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a></h3>
            <div class="row">
                <b class="price-tag"><?= money($product['price']) ?></b>
                <?php if (!$isOutOfStock): ?>
                <form method="post" action="<?= url('cart.php') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <button class="btn sm" type="submit">+ Add to Cart</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}
