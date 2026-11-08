<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Home';

$latest = $pdo->query('SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT 8')->fetchAll();
$cats = $pdo->query('SELECT category, COUNT(*) as n FROM products GROUP BY category ORDER BY category ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <span class="hero-badge">NEW COLLECTION NOW LIVE</span>
        <h1>Authentic Gear.<br><span>No Compromise.</span></h1>
        <p>Premium oversized hoodies, athletic kicks, everyday accessories, and tech hardware. Unmatched quality at honest prices with speedy nationwide delivery.</p>
        <div class="hero-actions">
            <a class="btn lg" href="<?= url('products.php') ?>">Explore Collections &rarr;</a>
            <button class="btn lg ghost-light" type="button" onclick="document.getElementById('ai-toggle').click()">&#10024; Ask Shopping AI</button>
        </div>
    </div>
</section>

<section class="container section">
    <div class="head-row">
        <div>
            <h2>Shop by Category</h2>
            <p class="muted">Browse curated apparel, footwear, and tech accessories.</p>
        </div>
    </div>
    <div class="cats">
        <?php foreach ($cats as $c): ?>
            <a class="cat-card" href="<?= url('products.php?category=' . urlencode($c['category'])) ?>">
                <b><?= e($c['category']) ?></b>
                <small><?= (int)$c['n'] ?> items</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section">
    <div class="head-row">
        <div>
            <h2>Latest Drops</h2>
            <p class="muted">Freshly added streetwear and gear in stock today.</p>
        </div>
        <a class="link-arrow" href="<?= url('products.php') ?>">View all products &rarr;</a>
    </div>
    
    <div class="grid">
        <?php foreach ($latest as $p) echo product_card($p); ?>
        <?php if (!$latest): ?>
            <p class="empty">No products are currently available. Check back soon!</p>
        <?php endif; ?>
    </div>
</section>

<section class="container section perks">
    <div class="perk-card">
        <div class="perk-icon">&#9889;</div>
        <b>Express Delivery</b>
        <span>Delivered in 2 to 4 business days nationwide.</span>
    </div>
    <div class="perk-card">
        <div class="perk-icon">&#128176;</div>
        <b>Cash on Delivery</b>
        <span>Inspect your parcel and pay upon arrival at your door.</span>
    </div>
    <div class="perk-card">
        <div class="perk-icon">&#128260;</div>
        <b>Hassle-Free Returns</b>
        <span>7 days return &amp; exchange guarantee on eligible items.</span>
    </div>
    <div class="perk-card">
        <div class="perk-icon">&#10024;</div>
        <b>AI Shopping Assistant</b>
        <span>Need size advice or gift ideas? Ask our smart advisor anytime.</span>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
