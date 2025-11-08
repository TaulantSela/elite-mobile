<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$categoryQuery = 'SELECT categoryid, categoryname FROM category ORDER BY categoryname ASC';
$categoryResult = mysqli_query($conn, $categoryQuery);
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
        <li class="breadcrumb-item">View</li>
        <li class="breadcrumb-item active">Categories</li>
      </ol>
  <div class="card mb-3">
    <a href="admin_insertcategory.php" class="btn btn-danger" role="button">+ Add Category</a>
        <div class="card-header">
          <i class="fa fa-table"></i> All Categories</div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr align="center">
                        <th>#</th>
                        <th>Category Title</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($categoryResult): ?>
        <?php $rowNumber = 0; ?>
        <?php while ($row = mysqli_fetch_assoc($categoryResult)): ?>
            <?php
            $rowNumber++;
            $categoryId = (int) $row['categoryid'];
            $categoryTitle = htmlspecialchars($row['categoryname'], ENT_QUOTES, 'UTF-8');
            ?>
            <tr align="center">
                <td><?php echo $rowNumber; ?></td>
                <td><?php echo $categoryTitle; ?></td>
                <td><a href="admin_editcategory.php?edit_cat=<?php echo $categoryId; ?>"><i class="fa fa-edit"></i> Edit </a></td>
                <td><a href="admin_deletecategory.php?delete_cat=<?php echo $categoryId; ?>" onclick="return confirm('Delete this category?');"><i class="fa fa-close"></i> Delete</a></td>
            </tr>
        <?php endwhile; ?>
        <?php mysqli_free_result($categoryResult); ?>
    <?php else: ?>
            <tr>
                <td colspan="4" class="text-center text-danger">Unable to load categories.</td>
            </tr>
    <?php endif; ?>
                </tbody>
              </table>
            </div>
      </div>
        </div>
    </div>
      <?php require_once __DIR__ . '/../includes/admin/footer.html'; ?>
      <?php require_once __DIR__ . '/../includes/admin/scripts.html'; ?>
  </div>
</body>
</html>