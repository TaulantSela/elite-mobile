<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$categoryQuery = 'SELECT c.categoryid, c.categoryname, COUNT(p.productid) AS product_count
                  FROM category c LEFT JOIN product p ON p.categoryid = c.categoryid
                  GROUP BY c.categoryid, c.categoryname
                  ORDER BY c.categoryname ASC';
$categoryResult = mysqli_query($conn, $categoryQuery);

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Categories';
$adminActive = 'categories';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Brands &amp; categories</h2>
            <p><?php echo $categoryResult ? (int) mysqli_num_rows($categoryResult) : 0; ?> categories</p>
        </div>
        <a class="abtn abtn-grad" href="<?php echo $e(elite_asset('admin/admin_insertcategory.php')); ?>"><i class="fa-solid fa-plus"></i> Add category</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>#</th><th>Category</th><th>Products</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($categoryResult && mysqli_num_rows($categoryResult) > 0): ?>
                    <?php $rowNumber = 0; ?>
                    <?php while ($row = mysqli_fetch_assoc($categoryResult)): ?>
                        <?php
                        $rowNumber++;
                        $categoryId = (int) $row['categoryid'];
                        ?>
                        <tr>
                            <td><?php echo $rowNumber; ?></td>
                            <td class="t-name"><?php echo $e($row['categoryname']); ?></td>
                            <td><span class="a-tag"><?php echo (int) $row['product_count']; ?> products</span></td>
                            <td>
                                <div class="row-actions" style="justify-content:flex-end;">
                                    <a class="icon-btn" title="Edit" href="admin_editcategory.php?edit_cat=<?php echo $categoryId; ?>"><i class="fa-solid fa-pen"></i></a>
                                    <a class="icon-btn danger" title="Delete" href="admin_deletecategory.php?delete_cat=<?php echo $categoryId; ?>" onclick="return confirm('Delete this category?');"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php mysqli_free_result($categoryResult); ?>
                <?php else: ?>
                    <tr><td colspan="4"><div class="a-empty"><i class="fa-solid fa-layer-group"></i> No categories yet.</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
