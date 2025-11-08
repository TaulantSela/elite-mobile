<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$categoryId = isset($_GET['edit_cat']) ? (int) $_GET['edit_cat'] : 0;
$categoryTitle = '';
$error = '';

if ($categoryId <= 0) {
    header('Location: admin_categories.php');
    exit;
}

$selectStatement = mysqli_prepare($conn, 'SELECT categoryname FROM category WHERE categoryid = ?');

if ($selectStatement && mysqli_stmt_bind_param($selectStatement, 'i', $categoryId) && mysqli_stmt_execute($selectStatement)) {
    $result = mysqli_stmt_get_result($selectStatement);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($row) {
        $categoryTitle = $row['categoryname'];
    } else {
        mysqli_stmt_close($selectStatement);
        header('Location: admin_categories.php');
        exit;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($selectStatement);
} else {
    if ($selectStatement) {
        mysqli_stmt_close($selectStatement);
    }
    header('Location: admin_categories.php?error=not_found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cat'])) {
    $newCategoryTitle = trim($_POST['new_cat'] ?? '');

    if ($newCategoryTitle === '') {
        $error = 'Category name cannot be empty.';
    } else {
        $updateStatement = mysqli_prepare($conn, 'UPDATE category SET categoryname = ? WHERE categoryid = ?');

        if ($updateStatement && mysqli_stmt_bind_param($updateStatement, 'si', $newCategoryTitle, $categoryId) && mysqli_stmt_execute($updateStatement)) {
            mysqli_stmt_close($updateStatement);
            header('Location: admin_categories.php');
            exit;
        }

        if ($updateStatement) {
            mysqli_stmt_close($updateStatement);
        }

        $error = 'Unable to update category. Please try again.';
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
        <li class="breadcrumb-item">Edit</li>
        <li class="breadcrumb-item active">Category</li>
      </ol>
  <form action="" method="post" style="padding:80px;" autocomplete="off">
<b>Update Category:</b>
<input type="text" name="new_cat" value="<?php echo htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8'); ?>" required />
<input type="submit" name="update_cat" value="Update Category" />
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