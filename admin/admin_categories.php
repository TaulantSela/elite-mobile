<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION["username"])) {
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
                        <th>Category ID</th>
                        <th>Category Title</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
	<?php
	include("../includes/db_connection.php");
	$get_cat = "select * from category";
	$run_cat = mysqli_query($conn, $get_cat);
	$i = 0;
	while ($row_cat=mysqli_fetch_array($run_cat)){
		$cat_id = $row_cat['categoryid'];
		$cat_title = $row_cat['categoryname'];
		$i++;
	?>
	<tr align="center">
		<td><?php echo $i;?></td>
		<td><?php echo $cat_title;?></td>
		<td><a href="admin_editcategory.php?edit_cat=<?php echo $cat_id; ?>"><i class="fa fa-edit"></i> Edit </a></td>
		<td><a href="admin_deletecategory.php?delete_cat=<?php echo $cat_id;?>"><i class="fa fa-close"></i> Delete</a></td>
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