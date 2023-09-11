<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION["username"])) {
  echo "<script>window.open('LoginForm.php','_self')</script>";
} else {
  include("../includes/db_connection.php");
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
    $get_id = $_GET['edit_pro'];
    $get_pro = "select * from product where productid='$get_id'";
    $run_pro = mysqli_query($conn, $get_pro);
    $i = 0;
    $row_pro=mysqli_fetch_array($run_pro);
    $pro_id = $row_pro['productid'];
    $pro_title = $row_pro['productname'];
    $pro_cat = $row_pro['categoryid'];
    $pro_price = $row_pro['price'];
    $pro_image = $row_pro['image'];
    $pro_desc = $row_pro['info'];
    $get_cat = "select * from category where categoryid='$pro_cat'";
    $run_cat=mysqli_query($conn, $get_cat);
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
	    $update_product = "UPDATE product SET price = '$product_price', info = '$product_desc' where productid='$update_id'";
		$run_product = mysqli_query($conn, $update_product);
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