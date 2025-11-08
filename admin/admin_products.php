<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

$productQuery = 'SELECT productid, productname, image, price FROM product ORDER BY productname ASC';
$productResult = mysqli_query($conn, $productQuery);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/../includes/admin_head.html'; ?>
<body class="fixed-nav sticky-footer bg-dark" id="page-top">
<?php require_once __DIR__ . '/../includes/admin_header.html'; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">View</li>
        <li class="breadcrumb-item active">Products</li>
      </ol>
<div class="card mb-3">
    <a href="admin_insertproduct.php" class="btn btn-danger" role="button">+ Add Product</a>
        <div class="card-header">
          <i class="fa fa-table"></i> All Products</div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr align="center" >
                  <th>#</th>
                  <th>Title</th>
                  <th>Image</th>
                  <th>Price</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <tbody>
<?php if ($productResult): ?>
    <?php $rowNumber = 0; ?>
    <?php while ($row = mysqli_fetch_assoc($productResult)): ?>
        <?php
        $rowNumber++;
        $productId = (int) $row['productid'];
        $productTitle = htmlspecialchars($row['productname'], ENT_QUOTES, 'UTF-8');
        $productImage = htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8');
        $productPrice = number_format((float) $row['price'], 2);
        ?>
        <tr align="center">
            <td><?php echo $rowNumber; ?></td>
            <td><?php echo $productTitle; ?></td>
            <td><img src="../img/<?php echo $productImage; ?>" height="60" alt="<?php echo $productTitle; ?>" /></td>
            <td><?php echo $productPrice; ?> den</td>
            <td><a href="admin_editproduct.php?edit_pro=<?php echo $productId; ?>">Edit</a></td>
            <td><a href="admin_deleteproduct.php?delete_pro=<?php echo $productId; ?>" onclick="return confirm('Delete this product?');">Delete</a></td>
        </tr>
    <?php endwhile; ?>
    <?php mysqli_free_result($productResult); ?>
<?php else: ?>
        <tr>
            <td colspan="6" class="text-center text-danger">Unable to load products.</td>
        </tr>
<?php endif; ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>

    </div>
      <?php require_once __DIR__ . '/../includes/admin_footer.html'; ?>
      <?php require_once __DIR__ . '/../includes/admin_scripts.html'; ?>
  </div>
</body>
</html>