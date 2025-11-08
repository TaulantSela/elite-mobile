<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION["username"])) {
  header('Location: ../LoginForm.php');
  exit;
}
else {
    include("../includes/db_connection.php");
?>
<html lang="en">
<?php include("../includes/admin/head.html")?>
<body class="fixed-nav sticky-footer bg-dark" id="page-top">
<?php include("../includes/admin/header.html")?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Insert</li>
        <li class="breadcrumb-item active">ADD NEW PRODUCT</li>
      </ol>
  <form action="admin_insertproduct.php" method="post" enctype="multipart/form-data">
		<table align="center" width="795" border="0" bgcolor="white" cellpadding ="2px">
			<tr align="center">
				<td colspan="7"><h2>Insert New Product Here</h2></td>
			</tr>
			<tr>
				<td align="right"><b>Product Name:</b></td>
				<td><input type="text" name="productname" size="60" required/></td>
			</tr>
			<tr>
				<td align="right"><b>Product Category:</b></td>
				<td>
				<select name="categoryid" >
					<option>Select a Category</option>
					<?php
		$get_cats = "select * from category";
		$resultcats = mysqli_query($conn, $get_cats);
		while ($row_cats=mysqli_fetch_array($resultcats))
        {
            $cat_id = $row_cats['categoryid'];
		    $cat_name = $row_cats['categoryname'];
		    echo "<option value='$cat_id'>$cat_name</option>";
	    }
?>
</select>
		    </td>
		  </tr>
    	<tr>
        <td align="right"><b>Product Price:</b></td>
        <td><input type="text" name="price" required/></td>
      </tr>
			<tr>
				<td align="right"><b>Product Image:</b></td>
				<td><input type="file" name="image" /></td>
			</tr>
			<tr>
				<td align="right"><b>Product Info:</b></td>
				<td><textarea name="info" cols="30" rows="3"></textarea></td>
			</tr>
			<tr align="center">
				<td colspan="7"><input type="submit" name="insertpro" value="Insert Product Now"/></td>
			</tr>
		</table>
	</form>
<?php
	if(isset($_POST['insertpro'])){
		$product_title = $_POST['productname'];
		$product_cat= $_POST['categoryid'];
		$product_price = $_POST['price'];
		$product_info = $_POST['info'];
		$product_image = $_FILES['image']['name'];
		$product_image_tmp = $_FILES['image']['tmp_name'];
		move_uploaded_file($product_image_tmp,"../img/$product_image");
		$insert_product ="insert into product (`productname`, `categoryid`, `price`, `image`, `info`) values ('$product_title','$product_cat','$product_price', '$product_image','$product_info')";
		$insert_pro = mysqli_query($conn, $insert_product);
		if($insert_pro){
		echo "<script>alert('Product Has been inserted!')</script>";
		echo "<script>window.open('admin_insertproduct.php','_self')</script>";
		}
	}
?>
    </div>
      <?php include ("../includes/admin/footer.html")?>
      <?php include ("../includes/admin/scripts.html")?>
  </div>
</body>
</html>
<?php } ?>