<?php
require_once __DIR__ . '/includes/init.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM products WHERE id=?');
$st->execute([$id]);
$p = $st->fetch();

if (!$p) {
    http_response_code(404);
    $pageTitle = 'Product Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container section empty-state"><h2>Product Not Found</h2><p class="muted">The product you are looking for does not exist or has been removed.</p><a class="btn" href="' . url('products.php') . '">Browse All Products</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $p['name'];

// Related products
$rel = $pdo->prepare('SELECT * FROM products WHERE category=? AND id<>? ORDER BY RAND() LIMIT 4');
$rel->execute([$p['category'], $id]);
$related = $rel->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container section detail">
    <div class="detail-img">
        <?= product_thumb($p) ?>
    </div>
    
    <div class="detail-info">
        <div class="breadcrumbs">
            <a href="<?= url('index.php') ?>">Home</a> / 
            <a href="<?= url('products.php?category=' . urlencode($p['category'])) ?>"><?= e($p['category']) ?></a>
        </div>
        
        <h1><?= e($p['name']) ?></h1>
        <div class="price-hero"><?= money($p['price']) ?></div>
        
        <div class="stock-pill <?= $p['stock'] > 0 ? 'in' : 'out' ?>">
            <?php if ($p['stock'] > 5): ?>
                <span class="dot-in"></span> In Stock &amp; Ready to Ship
            <?php elseif ($p['stock'] > 0): ?>
                <span class="dot-warn"></span> Hurry! Only <?= (int)$p['stock'] ?> item<?= (int)$p['stock'] === 1 ? '' : 's' ?> remaining
            <?php else: ?>
                <span class="dot-out"></span> Currently Out of Stock
            <?php endif; ?>
        </div>

        <div class="desc-box">
            <h3>Product Overview</h3>
            <p><?= nl2br(e($p['description'])) ?></p>
        </div>

        <?php if ($p['stock'] > 0): ?>
        <form method="post" action="<?= url('cart.php') ?>" class="buy-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            
            <div class="qty-control">
                <label for="quantity">Qty:</label>
                <input id="quantity" type="number" name="quantity" value="1" min="1" max="<?= (int)$p['stock'] ?>">
            </div>
            
            <button class="btn lg add-to-cart-btn" type="submit">
                <span>Add to Cart</span>
            </button>
        </form>
        <?php else: ?>
            <button class="btn lg disabled" disabled>Sold Out</button>
        <?php endif; ?>

        <div class="detail-perks">
            <div>&#10003; 100% Genuine Guarantee</div>
            <div>&#10003; Free exchanges within 7 days</div>
            <div>&#10003; Cash on Delivery accepted</div>
        </div>
    </div>
</div>

<?php if ($related): ?>
<section class="container section">
    <h2>You May Also Like</h2>
    <div class="grid">
        <?php foreach ($related as $r) echo product_card($r); ?>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
