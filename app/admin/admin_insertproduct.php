<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/../includes/functions.php';

$categories = fetchAllCategories();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insertpro'])) {
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
    $extension = '';
    if ($imageProvided) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $errors[] = 'Only JPG, PNG, or WEBP images are allowed.';
        } elseif ($_FILES['image']['size'] > 4 * 1024 * 1024) {
            $errors[] = 'Image size must be under 4MB.';
        }
    } else {
        $errors[] = 'Please upload a primary product image.';
    }

    if (!$errors) {
        $slug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-') ?: 'product';
        $fileName = $slug . '-' . time() . '.' . $extension;
        $uploadsDir = __DIR__ . '/../img/products';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }
        if (!@move_uploaded_file($_FILES['image']['tmp_name'], $uploadsDir . '/' . $fileName)) {
            $errors[] = 'Could not store the image (uploads are not persisted on serverless hosting).';
        } else {
            $imagePath = 'products/' . $fileName;
        }
    }

    if (!$errors) {
        $statement = mysqli_prepare($conn, 'INSERT INTO product (productname, categoryid, price, image, info, paid) VALUES (?, ?, ?, ?, ?, 0)');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'siiss', $name, $categoryId, $price, $imagePath, $info);
            if (mysqli_stmt_execute($statement)) {
                $success = 'Product "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" has been added.';
            } else {
                $errors[] = 'Database error while inserting the product.';
            }
            mysqli_stmt_close($statement);
        } else {
            $errors[] = 'Database error while preparing the statement.';
        }
    }
}

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Add product';
$adminActive = 'add';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="admin-card" style="max-width:720px;">
    <div class="admin-card-head">
        <div>
            <h2>Add a new product</h2>
            <p>Publish a device to the storefront catalog.</p>
        </div>
        <a class="abtn abtn-ghost" href="<?php echo $e(elite_asset('admin/admin_products.php')); ?>"><i class="fa-solid fa-arrow-left"></i> Back to products</a>
    </div>
    <div class="admin-card-body">
        <?php if ($errors): ?>
            <div class="a-alert error"><i class="fa-solid fa-circle-exclamation"></i>
                <ul><?php foreach ($errors as $err): ?><li><?php echo $e($err); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <form class="admin-form" action="admin_insertproduct.php" method="post" enctype="multipart/form-data">
            <div class="field">
                <label for="productname">Product name</label>
                <input type="text" id="productname" name="productname" placeholder="e.g. iPhone 17 Pro Max" required>
            </div>
            <div class="field">
                <label for="categoryid">Category</label>
                <select id="categoryid" name="categoryid" required>
                    <option value="">Select a category</option>
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
                <textarea id="info" name="info" placeholder="<p><strong>Display</strong>: 6.9&quot; OLED, 120Hz</p>"></textarea>
            </div>
            <button type="submit" name="insertpro" class="abtn abtn-grad"><i class="fa-solid fa-plus"></i> Insert product</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
