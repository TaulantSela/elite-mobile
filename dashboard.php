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
            mkdir($uploadsDirectory, 0755, true);
        }
        $targetPath = $uploadsDirectory . '/' . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $errors[] = 'Failed to store the uploaded image. Please try again.';
        } else {
            $imagePath = 'products/' . $fileName;
        }
    }

    if (!$errors && $imagePath !== '') {
        $statement = mysqli_prepare($conn, 'INSERT INTO product (productname, categoryid, price, image, info, paid) VALUES (?, ?, ?, ?, ?, 0)');
        if ($statement === false) {
            $errors[] = 'Database error while preparing the statement: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($statement, 'siiss', $name, $categoryId, $price, $imagePath, $info);
            if (!mysqli_stmt_execute($statement)) {
                $errors[] = 'Database error while inserting the product: ' . mysqli_stmt_error($statement);
            } else {
                $successMessage = 'Product "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" has been added successfully.';
            }
            mysqli_stmt_close($statement);
        }
    }
}

$recentProductsQuery = 'SELECT p.productid, p.productname, p.price, p.image, c.categoryname FROM product p JOIN category c ON p.categoryid = c.categoryid ORDER BY p.productid DESC LIMIT 10';
$recentResult = mysqli_query($conn, $recentProductsQuery);
$recentProducts = [];
if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentProducts[] = $row;
    }
    mysqli_free_result($recentResult);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/admin/head.html'; ?>
<body class="bg-light">
<?php require_once __DIR__ . '/includes/admin/header.html'; ?>
<main class="container py-4">
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 card-title mb-3">Quick add product</h2>
                    <p class="text-muted mb-4">Publish a new flagship in just a few clicks. Images are stored under <code>img/products</code>.</p>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success">
                            <?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="productname">Product name</label>
                            <input type="text" class="form-control" id="productname" name="productname" placeholder="e.g. iPhone 17 Pro Max" required>
                        </div>
                        <div class="form-group">
                            <label for="categoryid">Category</label>
                            <select class="form-control" id="categoryid" name="categoryid" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (MKD)</label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="e.g. 109990" required>
                        </div>
                        <div class="form-group">
                            <label for="image">Primary image</label>
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                            <small class="form-text text-muted">JPG, PNG, or WEBP — max 4MB.</small>
                        </div>
                        <div class="form-group">
                            <label for="info">Key highlights (HTML allowed)</label>
                            <textarea class="form-control" id="info" name="info" rows="4" placeholder="&lt;p&gt;&lt;strong&gt;Announced&lt;/strong&gt;: 2025 March&lt;/p&gt;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Publish product</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 card-title mb-0">Latest products</h2>
                            <small class="text-muted">Recently published devices (latest 10).</small>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="/admin/admin_products.php">Manage all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Image</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($recentProducts): ?>
                                <?php foreach ($recentProducts as $product): ?>
                                    <tr>
                                        <td><?php echo (int) $product['productid']; ?></td>
                                        <td><?php echo htmlspecialchars($product['productname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($product['categoryname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo formatPrice((int) $product['price']); ?></td>
                                        <td>
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="/img/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['productname'], ENT_QUOTES, 'UTF-8'); ?>" width="60" class="rounded">
                                            <?php else: ?>
                                                <span class="text-muted">n/a</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No products found yet. Start by adding one above.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
