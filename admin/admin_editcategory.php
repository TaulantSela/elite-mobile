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
        <li class="breadcrumb-item active">Category</li>
      </ol>
  <?php
include("../includes/db_connection.php");
if(isset($_GET['edit_cat'])){
	$cat_id = (int) $_GET['edit_cat'];
	$stmt_cat = mysqli_prepare($conn, "SELECT * FROM category WHERE categoryid = ?");
	mysqli_stmt_bind_param($stmt_cat, "i", $cat_id);
	mysqli_stmt_execute($stmt_cat);
	$run_cat = mysqli_stmt_get_result($stmt_cat);
	$row_cat = mysqli_fetch_array($run_cat);
	$cat_id = $row_cat['categoryid'];
	$cat_title = $row_cat['categoryname'];
}
?>
<form action="" method="post" style="padding:80px;">
<b>Update Category:</b>
<input type="text" name="new_cat" value="<?php echo $cat_title;?>"/>
<input type="submit" name="update_cat" value="Update Category" />
</form>
<?php
	if(isset($_POST['update_cat'])) {
  	    $update_id = $cat_id;
  	    $new_cat = $_POST['new_cat'];
  	    $stmt = mysqli_prepare($conn, "UPDATE category SET categoryname = ? WHERE categoryid = ?");
  	mysqli_stmt_bind_param($stmt, "si", $new_cat, $update_id);
  	$run_cat = mysqli_stmt_execute($stmt);
  	mysqli_stmt_close($stmt);
  	    if($run_cat)
            {
    	        echo "<script>alert(' Category has been updated!')</script>";
    	        echo "<script>window.open('admin_categories.php','_self')</script>";
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