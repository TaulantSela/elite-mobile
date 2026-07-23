<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$categoryId = getSelectedCategoryId();
$search     = getShopSearch();
$sort       = getShopSort();

$brands   = fetchBrandsWithCounts();
$products = fetchProducts($categoryId, $search, $sort);

$activeBrand = null;
foreach ($brands as $brand) {
    if ($brand['id'] === $categoryId) {
        $activeBrand = $brand['name'];
        break;
    }
}

$heading = $activeBrand !== null ? $activeBrand . ' phones' : 'All phones';
if ($search !== '') {
    $heading = 'Results for “' . $search . '”';
}

/** Build a shop URL that preserves the other active filters. */
$shopUrl = static function (?int $cat, string $q, string $s): string {
    $params = [];
    if ($cat !== null && $cat > 0) {
        $params['categoryid'] = $cat;
    }
    if ($q !== '') {
        $params['query'] = $q;
    }
    if ($s !== 'newest') {
        $params['sort'] = $s;
    }

    return 'products.php' . ($params !== [] ? '?' . http_build_query($params) : '');
};

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$sortLabels = [
    'newest'     => 'Newest first',
    'price_desc' => 'Price: high to low',
    'price_asc'  => 'Price: low to high',
    'name'       => 'Name: A to Z',
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="home.php">Home</a>
            <span class="sep">/</span>
            <span>Phones<?php echo $activeBrand !== null ? ' <span class="sep">/</span> ' . $e($activeBrand) : ''; ?></span>
        </nav>
        <h1><?php echo $e($heading); ?></h1>
        <p>Browse the latest flagship devices across every ecosystem — filter by brand, sort by price, and find your next upgrade.</p>
    </div>
</section>

<main class="shop">
    <div class="container">
        <!-- Brand filter chips -->
        <div class="filter-chips" role="group" aria-label="Filter by brand">
            <a class="chip <?php echo $categoryId === null ? 'is-active' : ''; ?>" href="<?php echo $e($shopUrl(null, $search, $sort)); ?>">
                All brands
            </a>
            <?php foreach ($brands as $brand): ?>
                <a class="chip <?php echo $categoryId === $brand['id'] ? 'is-active' : ''; ?>"
                   href="<?php echo $e($shopUrl($brand['id'], $search, $sort)); ?>">
                    <?php echo $e($brand['name']); ?>
                    <span class="count"><?php echo (int) $brand['count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Toolbar: count + search + sort -->
        <div class="shop-toolbar">
            <p class="results-count"><strong><?php echo count($products); ?></strong> <?php echo count($products) === 1 ? 'device' : 'devices'; ?> found</p>
            <div class="shop-controls">
                <form class="shop-search" action="products.php" method="get" role="search">
                    <?php if ($categoryId !== null): ?><input type="hidden" name="categoryid" value="<?php echo (int) $categoryId; ?>"><?php endif; ?>
                    <?php if ($sort !== 'newest'): ?><input type="hidden" name="sort" value="<?php echo $e($sort); ?>"><?php endif; ?>
                    <input type="text" name="query" value="<?php echo $e($search); ?>" placeholder="Search phones…" aria-label="Search phones">
                    <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <form method="get" action="products.php">
                    <?php if ($categoryId !== null): ?><input type="hidden" name="categoryid" value="<?php echo (int) $categoryId; ?>"><?php endif; ?>
                    <?php if ($search !== ''): ?><input type="hidden" name="query" value="<?php echo $e($search); ?>"><?php endif; ?>
                    <label class="sr-only" for="sort">Sort by</label>
                    <select id="sort" name="sort" class="sort-select" onchange="this.form.submit()">
                        <?php foreach ($sortLabels as $value => $label): ?>
                            <option value="<?php echo $e($value); ?>" <?php echo $sort === $value ? 'selected' : ''; ?>><?php echo $e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <?php if ($products === []): ?>
            <div class="empty-state">
                <i class="fa-solid fa-mobile-screen-button"></i>
                <h3>No devices match your filters</h3>
                <p>Try a different brand or clear your search.</p>
                <p style="margin-top:1.25rem;"><a class="btn-dark" href="products.php"><i class="fa-solid fa-rotate-left"></i> Reset filters</a></p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a class="card-link" href="description.php?productid=<?php echo (int) $product['id']; ?>" aria-label="<?php echo $e($product['name']); ?>"></a>
                        <div class="product-photo">
                            <img src="<?php echo $e(product_image_src($product['image'])); ?>" alt="<?php echo $e($product['name']); ?>">
                        </div>
                        <div class="product-body">
                            <span class="product-brand"><?php echo $e($product['category']); ?></span>
                            <h3 class="product-name"><?php echo $e($product['name']); ?></h3>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                            <div class="product-actions">
                                <a class="btn-view" href="description.php?productid=<?php echo (int) $product['id']; ?>">
                                    View details <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
