<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/admin_init.php';
require_once __DIR__ . '/includes/functions.php';

$categories = fetchAllCategories();
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['productname'] ?? '');
    $categoryId = (int) ($_POST['categoryid'] ?? 0);
    $priceInput = preg_replace('/[^0-9]/', '', $_POST['price'] ?? '');
    $price = $priceInput !== '' ? (int) $priceInput : 0;
    $info = trim($_POST['info'] ?? '');

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a category.';
    }
    if ($price <= 0) {
        $errors[] = 'Please enter a valid price in MKD.';
    }

    $imagePath = '';
    $imageProvided = isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name']);

    if (!$imageProvided) {
        $errors[] = 'Please upload a primary product image.';
    }

    if (!$errors && $imageProvided) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Only JPG, PNG, or WEBP images are allowed.';
        } elseif ($_FILES['image']['size'] > 4 * 1024 * 1024) {
            $errors[] = 'Image size must be under 4MB.';
        }
    }

    if (!$errors) {
        $slugBase = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
        $slugBase = trim($slugBase, '-');
        if ($slugBase === '') {
            $slugBase = 'product';
        }
        $fileName = $slugBase . '-' . time() . '.' . ($extension ?? 'jpg');
        $uploadsDirectory = __DIR__ . '/img/products';
        if (!is_dir($uploadsDirectory)) {
            @mkdir($uploadsDirectory, 0755, true);
        }
        $targetPath = $uploadsDirectory . '/' . $fileName;

        if (!@move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $errors[] = 'Failed to store the uploaded image. Please try again.';
        } else {
            $imagePath = 'products/' . $fileName;
        }
    }

    if (!$errors && $imagePath !== '') {
        $statement = mysqli_prepare($conn, 'INSERT INTO product (productname, categoryid, price, image, info, paid) VALUES (?, ?, ?, ?, ?, 0)');
        if ($statement === false) {
            $errors[] = 'Database error while preparing the statement.';
        } else {
            mysqli_stmt_bind_param($statement, 'siiss', $name, $categoryId, $price, $imagePath, $info);
            if (!mysqli_stmt_execute($statement)) {
                $errors[] = 'Database error while inserting the product.';
            } else {
                $successMessage = 'Product "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" has been added successfully.';
            }
            mysqli_stmt_close($statement);
        }
    }
}

/** Quick stats. */
$scalar = static function (mysqli $c, string $sql): int {
    $r = mysqli_query($c, $sql);
    $row = $r ? mysqli_fetch_row($r) : null;
    return (int) ($row[0] ?? 0);
};
$totalProducts = $scalar($conn, 'SELECT COUNT(*) FROM product');
$totalCategories = count($categories);
$totalServices = $scalar($conn, 'SELECT COUNT(*) FROM services');
$avgPrice = $scalar($conn, 'SELECT COALESCE(AVG(price), 0) FROM product');

$recentProductsQuery = 'SELECT p.productid, p.productname, p.price, p.image, c.categoryname FROM product p JOIN category c ON p.categoryid = c.categoryid ORDER BY p.productid DESC LIMIT 8';
$recentResult = mysqli_query($conn, $recentProductsQuery);
$recentProducts = [];
if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentProducts[] = $row;
    }
    mysqli_free_result($recentResult);
}

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Dashboard';
$adminActive = 'dashboard';
require __DIR__ . '/includes/admin/layout_top.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-ic"><i class="fa-solid fa-mobile-screen"></i></span>
        <div><strong><?php echo $totalProducts; ?></strong><span>Products</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-ic violet"><i class="fa-solid fa-layer-group"></i></span>
        <div><strong><?php echo $totalCategories; ?></strong><span>Categories</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-ic green"><i class="fa-solid fa-screwdriver-wrench"></i></span>
        <div><strong><?php echo $totalServices; ?></strong><span>Services</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-ic amber"><i class="fa-solid fa-tags"></i></span>
        <div><strong><?php echo formatPrice($avgPrice); ?></strong><span>Avg. price</span></div>
    </div>
</div>

<div class="admin-grid cols-2">
    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Quick add product</h2>
                <p>Publish a new device in a few clicks.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <?php if ($errors): ?>
                <div class="a-alert error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul><?php foreach ($errors as $err): ?><li><?php echo $e($err); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if ($successMessage): ?>
                <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $successMessage; ?></div>
            <?php endif; ?>
            <form class="admin-form" method="post" enctype="multipart/form-data">
                <div class="field">
                    <label for="productname">Product name</label>
                    <input type="text" id="productname" name="productname" placeholder="e.g. iPhone 17 Pro Max" required>
                </div>
                <div class="field">
                    <label for="categoryid">Category</label>
                    <select id="categoryid" name="categoryid" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int) $category['id']; ?>"><?php echo $e($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="price">Price (MKD)</label>
                    <input type="text" id="price" name="price" placeholder="e.g. 109990" required>
                </div>
                <div class="field">
                    <label for="image">Primary image</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <p class="hint">JPG, PNG, or WEBP — max 4MB.</p>
                </div>
                <div class="field">
                    <label for="info">Key highlights (HTML allowed)</label>
                    <textarea id="info" name="info" placeholder="<p><strong>Announced</strong>: 2025 March</p>"></textarea>
                </div>
                <button type="submit" class="abtn abtn-grad abtn-block"><i class="fa-solid fa-plus"></i> Publish product</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Latest products</h2>
                <p>Recently published devices.</p>
            </div>
            <a class="abtn abtn-ghost" href="<?php echo $e(elite_asset('admin/admin_products.php')); ?>">Manage all</a>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Product</th><th>Category</th><th>Price</th><th>Image</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($recentProducts): ?>
                            <?php foreach ($recentProducts as $product): ?>
                                <tr>
                                    <td><?php echo (int) $product['productid']; ?></td>
                                    <td class="t-name"><?php echo $e($product['productname']); ?></td>
                                    <td><span class="a-tag"><?php echo $e($product['categoryname']); ?></span></td>
                                    <td class="t-price"><?php echo formatPrice((int) $product['price']); ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img class="prod-thumb" src="<?php echo $e(elite_asset('img/' . $product['image'])); ?>" alt="<?php echo $e($product['productname']); ?>">
                                        <?php else: ?>
                                            <span class="hint">n/a</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5"><div class="a-empty"><i class="fa-solid fa-box-open"></i> No products yet — add one on the left.</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/admin/layout_bottom.php'; ?>
