<!DOCTYPE html>
<?php 
session_start(); 

if(!isset($_SESSION["username"])){
  echo "<script>window.open('LoginForm.php','_self')</script>";
}
else {
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
                  <th>S.N</th>
                  <th>Title</th>
                  <th>Image</th>
                  <th>Price</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
<?php
	include("../includes/db_connection.php");
	$get_pro = "select * from product";
	$result = mysqli_query($conn, $get_pro);
	$i = 0;
	while ($row_pro=mysqli_fetch_array($result)){
		$pro_id = $row_pro['productid'];
		$pro_title = $row_pro['productname'];
		$pro_image = $row_pro['image'];
		$pro_price = $row_pro['price'];
		$i++;
	?>
	<tr align="center">
		<td><?php echo $i;?></td>
		<td><?php echo $pro_title;?></td>
		<td><img src="../img/<?php echo $pro_image;?>" height="60"/></td>
		<td><?php echo $pro_price." den";?></td>
		<td><a href="admin_editproduct.php?edit_pro=<?php echo $pro_id; ?>">Edit</a></td>
		<td><a href="admin_deleteproduct.php?delete_pro=<?php echo $pro_id;?>">Delete</a></td>
	</tr>
	<?php } ?>
</table>
            </div>
          </div>
        </div>

    </div>
      <?php include ("../includes/admin_footer.html")?>
      <?php include ("../includes/admin_scripts.html")?>
  </div>
</body>
</html>
<?php } ?>