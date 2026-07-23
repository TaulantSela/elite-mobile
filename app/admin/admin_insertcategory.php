<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    $name = trim($_POST['new_cat'] ?? '');

    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $statement = mysqli_prepare($conn, 'INSERT INTO category (categoryname) VALUES (?)');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 's', $name);
            if (mysqli_stmt_execute($statement)) {
                $success = 'Category "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" has been added.';
            } else {
                $errors[] = 'Database error while inserting the category.';
            }
            mysqli_stmt_close($statement);
        } else {
            $errors[] = 'Database error while preparing the statement.';
        }
    }
}

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$adminTitle = 'Add category';
$adminActive = 'categories';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="admin-card" style="max-width:560px;">
    <div class="admin-card-head">
        <div>
            <h2>Add a new category</h2>
            <p>Create a brand or category for products.</p>
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
            <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <form class="admin-form" action="admin_insertcategory.php" method="post">
            <div class="field">
                <label for="new_cat">Category name</label>
                <input type="text" id="new_cat" name="new_cat" placeholder="e.g. Xiaomi" required>
            </div>
            <button type="submit" name="add_cat" class="abtn abtn-grad"><i class="fa-solid fa-plus"></i> Add category</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
