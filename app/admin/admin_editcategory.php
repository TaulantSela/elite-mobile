<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$errors = [];
$success = '';
$categoryId = (int) ($_GET['edit_cat'] ?? $_POST['categoryid'] ?? 0);

if ($categoryId <= 0) {
    header('Location: admin_categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cat'])) {
    $name = trim($_POST['new_cat'] ?? '');

    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $statement = mysqli_prepare($conn, 'UPDATE category SET categoryname = ? WHERE categoryid = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'si', $name, $categoryId);
            if (mysqli_stmt_execute($statement)) {
                $success = 'Category updated successfully.';
            } else {
                $errors[] = 'Database error while updating the category.';
            }
            mysqli_stmt_close($statement);
        } else {
            $errors[] = 'Database error while preparing the statement.';
        }
    }
}

$statement = mysqli_prepare($conn, 'SELECT categoryid, categoryname FROM category WHERE categoryid = ? LIMIT 1');
mysqli_stmt_bind_param($statement, 'i', $categoryId);
mysqli_stmt_execute($statement);
$category = mysqli_fetch_assoc(mysqli_stmt_get_result($statement)) ?: null;
mysqli_stmt_close($statement);

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Edit category';
$adminActive = 'categories';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<?php if ($category === null): ?>
    <div class="admin-card"><div class="admin-card-body"><div class="a-empty"><i class="fa-solid fa-triangle-exclamation"></i> Category not found. <a href="<?php echo $e(elite_asset('admin/admin_categories.php')); ?>">Back</a></div></div></div>
<?php else: ?>
    <div class="admin-card" style="max-width:560px;">
        <div class="admin-card-head">
            <div>
                <h2>Edit category</h2>
                <p><?php echo $e($category['categoryname']); ?></p>
            </div>
            <a class="abtn abtn-ghost" href="<?php echo $e(elite_asset('admin/admin_categories.php')); ?>"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="admin-card-body">
            <?php if ($errors): ?>
                <div class="a-alert error"><i class="fa-solid fa-circle-exclamation"></i>
                    <ul><?php foreach ($errors as $err): ?><li><?php echo $e($err); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $e($success); ?></div>
            <?php endif; ?>
            <form class="admin-form" action="admin_editcategory.php" method="post">
                <input type="hidden" name="categoryid" value="<?php echo (int) $category['categoryid']; ?>">
                <div class="field">
                    <label for="new_cat">Category name</label>
                    <input type="text" id="new_cat" name="new_cat" value="<?php echo $e($category['categoryname']); ?>" required>
                </div>
                <button type="submit" name="update_cat" class="abtn abtn-grad"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
