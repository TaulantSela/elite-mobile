<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    $newCategory = trim($_POST['new_cat'] ?? '');

    if ($newCategory === '') {
        $error = 'Category name is required.';
    } else {
        $statement = mysqli_prepare($conn, 'INSERT INTO category (categoryname) VALUES (?)');

        if ($statement && mysqli_stmt_bind_param($statement, 's', $newCategory) && mysqli_stmt_execute($statement)) {
            mysqli_stmt_close($statement);
            header('Location: admin_categories.php');
            exit;
        }

        if ($statement) {
            mysqli_stmt_close($statement);
        }

        $error = 'Unable to insert category. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../includes/admin/head.html'; ?>
<body class="fixed-nav sticky-footer bg-dark" id="page-top">
<?php require_once __DIR__ . '/../includes/admin/header.html'; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Insert</li>
        <li class="breadcrumb-item active">Add New Category</li>
      </ol>
  <form action="" method="post" style="padding:80px;" autocomplete="off">
<b>Insert New Category:</b>
<input type="text" name="new_cat" required />
<input type="submit" name="add_cat" value="Add Category" />
<?php if ($error !== ''): ?>
    <p class="text-danger" style="margin-top: 15px;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
</form>
    </div>
      <?php require_once __DIR__ . '/../includes/admin/footer.html'; ?>
      <?php require_once __DIR__ . '/../includes/admin/scripts.html'; ?>
  </div>
</body>
</html>