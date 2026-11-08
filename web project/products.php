<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Shop All Products';

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'new';

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(name LIKE ? OR description LIKE ? OR category LIKE ?)';
    $searchTerm = "%$q%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
}

$sortMap = [
    'new' => 'id DESC',
    'low' => 'price ASC',
    'high' => 'price DESC',
    'name' => 'name ASC'
];
$order = $sortMap[$sort] ?? 'id DESC';

$sql = 'SELECT * FROM products';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY $order";

$st = $pdo->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();

$cats = $pdo->query('SELECT DISTINCT category FROM products ORDER BY category ASC')->fetchAll(PDO::FETCH_COLUMN);

require __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div class="head-row">
        <div>
            <h2><?= $q !== '' ? 'Search Results for "' . e($q) . '"' : ($category !== '' ? e($category) : 'All Products') ?></h2>
            <p class="muted"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> found</p>
        </div>

        <form class="filters" method="get">
            <?php if ($q !== ''): ?>
                <input type="hidden" name="q" value="<?= e($q) ?>">
            <?php endif; ?>
            <select name="category" onchange="this.form.submit()" aria-label="Filter by category">
                <option value="">All Categories</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= e($c) ?>" <?= $c === $category ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort" onchange="this.form.submit()" aria-label="Sort products">
                <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest Arrivals</option>
                <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A to Z</option>
            </select>
        </form>
    </div>

    <div class="grid">
        <?php foreach ($products as $p) echo product_card($p); ?>
    </div>

    <?php if (!$products): ?>
        <div class="empty-state">
            <h3>No products found</h3>
            <p class="muted">We couldn't find any items matching your selected criteria.</p>
            <a class="btn" href="<?= url('products.php') ?>">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
