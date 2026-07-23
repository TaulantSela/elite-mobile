<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<!DOCTYPE html>
<?php
include("../includes/db_connection.php");
if (isset($_SESSION["username"])) {
?>
<html lang="en">
<?php include ("../includes/admin_head.html")?>
<body class="fixed-nav sticky-footer bg-dark" id="page-top">
<?php include("../includes/admin_header.html")?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Edit</li>
        <li class="breadcrumb-item active">Product</li>
      </ol>
  <?php
if(isset($_GET['edit_pro'])) {
    $get_id = (int) $_GET['edit_pro'];
    $stmt_pro = mysqli_prepare($conn, "SELECT * FROM product WHERE productid = ?");
	mysqli_stmt_bind_param($stmt_pro, "i", $get_id);
	mysqli_stmt_execute($stmt_pro);
	$run_pro = mysqli_stmt_get_result($stmt_pro);
    $i = 0;
    $row_pro=mysqli_fetch_array($run_pro);
    $pro_id = $row_pro['productid'];
    $pro_title = $row_pro['productname'];
    $pro_cat = $row_pro['categoryid'];
    $pro_price = $row_pro['price'];
    $pro_image = $row_pro['image'];
    $pro_desc = $row_pro['info'];
    $stmt_cat = mysqli_prepare($conn, "SELECT * FROM category WHERE categoryid = ?");
	mysqli_stmt_bind_param($stmt_cat, "i", $pro_cat);
	mysqli_stmt_execute($stmt_cat);
	$run_cat = mysqli_stmt_get_result($stmt_cat);
    $row_cat=mysqli_fetch_array($run_cat);
    $category_title = $row_cat['categoryname'];
}
?>
<html>
	<head>
		<title>Update Product</title>

<script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
<script>
        tinymce.init({selector:'textarea'});
</script>
	</head>
<body >
	<form action="" method="post" enctype="multipart/form-data">
		<table align="center" width="795" >
			<tr align="center">
				<td colspan="7"><h2>Edit & Update Product</h2></td>
			</tr>
            <tr>
				<td align="right"><b>Product Title:</b></td>
				<td><input readonly type="text" name="productname" size="60" value="<?php echo $pro_title;?>"/></td>
			</tr>
			<tr>
				<td align="right"><b>Product Price:</b></td>
				<td><input type="text" name="price" value="<?php echo $pro_price;?>"/></td>
			</tr>
			<tr>
				<td align="right"><b>Product Description:</b></td>
				<td><textarea name="info" cols="20" rows="10"><?php echo $pro_desc;?></textarea></td>
			</tr>
			<tr align="center">
				<td colspan="7"><input type="submit" name="update_product" value="Update Product"/></td>
			</tr>
		</table>
	</form>
</body>
</html>

<?php
	if(isset($_POST['update_product']))
  {
		$update_id = $pro_id;
		$product_price = $_POST['price'];
		$product_desc = $_POST['info'];
	    $stmt = mysqli_prepare($conn, "UPDATE product SET price = ?, info = ? WHERE productid = ?");
		mysqli_stmt_bind_param($stmt, "ssi", $product_price, $product_desc, $update_id);
		$run_product = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		if($run_product)
		{
  		    echo "<script>alert('Product has been updated!')</script>";
  		    echo "<script>window.open('admin_products.php','_self')</script>";
		}
	}
?>
    </div>
      <?php include ("../includes/admin_footer.html")?>
      <?php include ("../includes/admin_scripts.html")?>
  </div>
</body>
</html>
<?php } ?>