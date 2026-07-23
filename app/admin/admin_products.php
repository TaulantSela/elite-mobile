<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/../includes/functions.php';

$productQuery = 'SELECT p.productid, p.productname, p.image, p.price, c.categoryname
                 FROM product p JOIN category c ON p.categoryid = c.categoryid
                 ORDER BY p.productname ASC';
$productResult = mysqli_query($conn, $productQuery);

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Products';
$adminActive = 'products';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>All products</h2>
            <p><?php echo $productResult ? (int) mysqli_num_rows($productResult) : 0; ?> devices in the catalog</p>
        </div>
        <a class="abtn abtn-grad" href="<?php echo $e(elite_asset('admin/admin_insertproduct.php')); ?>"><i class="fa-solid fa-plus"></i> Add product</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>#</th><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($productResult && mysqli_num_rows($productResult) > 0): ?>
                    <?php $rowNumber = 0; ?>
                    <?php while ($row = mysqli_fetch_assoc($productResult)): ?>
                        <?php
                        $rowNumber++;
                        $productId = (int) $row['productid'];
                        ?>
                        <tr>
                            <td><?php echo $rowNumber; ?></td>
                            <td><img class="prod-thumb" src="<?php echo $e(elite_asset('img/' . $row['image'])); ?>" alt="<?php echo $e($row['productname']); ?>"></td>
                            <td class="t-name"><?php echo $e($row['productname']); ?></td>
                            <td><span class="a-tag"><?php echo $e($row['categoryname']); ?></span></td>
                            <td class="t-price"><?php echo formatPrice((int) $row['price']); ?></td>
                            <td>
                                <div class="row-actions" style="justify-content:flex-end;">
                                    <a class="icon-btn" title="Edit" href="admin_editproduct.php?edit_pro=<?php echo $productId; ?>"><i class="fa-solid fa-pen"></i></a>
                                    <a class="icon-btn danger" title="Delete" href="admin_deleteproduct.php?delete_pro=<?php echo $productId; ?>" onclick="return confirm('Delete this product?');"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php mysqli_free_result($productResult); ?>
                <?php else: ?>
                    <tr><td colspan="6"><div class="a-empty"><i class="fa-solid fa-box-open"></i> No products yet.</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
