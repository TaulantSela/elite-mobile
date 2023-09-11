<!DOCTYPE html>
<?php

session_start();

if (!isset($_SESSION["username"])) {

  echo "<script>window.open('LoginForm.php','_self')</script>";
}
else {


include("db_connection.php");

?>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>ELITEMobile - Admin Panel</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">
  <!-- Navigation-->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
    <img src="img/logo.png" class="img-thumbnail" width="80px" />
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav navbar-sidenav" id="exampleAccordion">
          <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Tables">
          <a class="nav-link" href="admin_products.php">
            <i class="fa fa-fw fa-list"></i>
            <span class="nav-link-text">Products</span>
          </a>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Charts">
          <a class="nav-link" href="admin_categories.php">
            <i class="fa fa-fw fa-list-alt"></i>
            <span class="nav-link-text">Categories </span>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav sidenav-toggler">
        <li class="nav-item">
          <a class="nav-link text-center" id="sidenavToggler">
            <i class="fa fa-fw fa-angle-left"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">


        <li class="nav-item">
          <a class="nav-link" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-fw fa-sign-out"></i>Logout</a>
        </li>
      </ul>
    </div>
  </nav>
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

		while ($row_cats=mysqli_fetch_array($resultcats)){

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

		move_uploaded_file($product_image_tmp,"img/$product_image");

		$insert_product ="insert into product (`productname`, `categoryid`, `price`, `image`, `info`) values ('$product_title','$product_cat','$product_price', '$product_image','$product_info')";
		$insert_pro = mysqli_query($conn, $insert_product);

		if($insert_pro){
		echo "<script>alert('Product Has been inserted!')</script>";
		echo "<script>window.open('admin_insertproduct.php','_self')</script>";
		}
	}
?>
    </div>
    <!-- /.container-fluid-->
    <!-- /.content-wrapper-->
    <footer class="sticky-footer">
      <div class="container">
        <div class="text-center">
          <small>ELITEMobile</small>
        </div>
      </div>
    </footer>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fa fa-angle-up"></i>
    </a>
    <!-- Logout Modal-->
      <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <a class="btn btn-primary" href="admin_logout.php">Logout</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Page level plugin JavaScript-->
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <!-- Custom scripts for this page-->
    <script src="js/sb-admin-datatables.min.js"></script>
    <script src="js/sb-admin-charts.min.js"></script>
  </div>
</body>

</html>
<?php } ?>
