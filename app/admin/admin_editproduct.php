<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/../includes/functions.php';

$categories = fetchAllCategories();
$errors = [];
$success = '';
$productId = (int) ($_GET['edit_pro'] ?? $_POST['productid'] ?? 0);

if ($productId <= 0) {
    header('Location: admin_products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $categoryId = (int) ($_POST['categoryid'] ?? 0);
    $priceInput = preg_replace('/[^0-9]/', '', $_POST['price'] ?? '');
    $price = $priceInput !== '' ? (int) $priceInput : 0;
    $info = trim($_POST['info'] ?? '');

    if ($categoryId <= 0) {
        $errors[] = 'Please select a category.';
    }
    if ($price <= 0) {
        $errors[] = 'Please enter a valid price in MKD.';
    }

    if (!$errors) {
        $statement = mysqli_prepare($conn, 'UPDATE product SET categoryid = ?, price = ?, info = ? WHERE productid = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'iisi', $categoryId, $price, $info, $productId);
            if (mysqli_stmt_execute($statement)) {
                $success = 'Product updated successfully.';
            } else {
                $errors[] = 'Database error while updating the product.';
            }
            mysqli_stmt_close($statement);
        } else {
            $errors[] = 'Database error while preparing the statement.';
        }
    }
}

// Load current product
$statement = mysqli_prepare($conn, 'SELECT productid, productname, categoryid, price, image, info FROM product WHERE productid = ? LIMIT 1');
mysqli_stmt_bind_param($statement, 'i', $productId);
mysqli_stmt_execute($statement);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($statement)) ?: null;
mysqli_stmt_close($statement);

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Edit product';
$adminActive = 'products';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<?php if ($product === null): ?>
    <div class="admin-card"><div class="admin-card-body"><div class="a-empty"><i class="fa-solid fa-triangle-exclamation"></i> Product not found. <a href="<?php echo $e(elite_asset('admin/admin_products.php')); ?>">Back to products</a></div></div></div>
<?php else: ?>
    <div class="admin-card" style="max-width:720px;">
        <div class="admin-card-head">
            <div>
                <h2>Edit product</h2>
                <p><?php echo $e($product['productname']); ?></p>
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
                <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $e($success); ?>
                    <a href="<?php echo $e(elite_asset('admin/admin_products.php')); ?>" style="margin-left:auto;font-weight:600;">View all →</a>
                </div>
            <?php endif; ?>
            <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;">
                <img class="prod-thumb" style="width:120px;height:120px;" src="<?php echo $e(elite_asset('img/' . $product['image'])); ?>" alt="<?php echo $e($product['productname']); ?>">
                <form class="admin-form" method="post" action="admin_editproduct.php" style="flex:1;min-width:280px;">
                    <input type="hidden" name="productid" value="<?php echo (int) $product['productid']; ?>">
                    <div class="field">
                        <label for="productname">Product name</label>
                        <input type="text" id="productname" value="<?php echo $e($product['productname']); ?>" readonly>
                        <p class="hint">Product name can't be changed here.</p>
                    </div>
                    <div class="field">
                        <label for="categoryid">Category</label>
                        <select id="categoryid" name="categoryid" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo (int) $category['id'] === (int) $product['categoryid'] ? 'selected' : ''; ?>><?php echo $e($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="price">Price (MKD)</label>
                        <input type="text" id="price" name="price" value="<?php echo (int) $product['price']; ?>" required>
                    </div>
                    <div class="field">
                        <label for="info">Key highlights (HTML allowed)</label>
                        <textarea id="info" name="info" style="min-height:160px;"><?php echo $e((string) $product['info']); ?></textarea>
                    </div>
                    <button type="submit" name="update_product" class="abtn abtn-grad"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
