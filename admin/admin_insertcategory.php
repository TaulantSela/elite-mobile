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
        <li class="breadcrumb-item">Insert</li>
        <li class="breadcrumb-item active">ADD NEW CATEGORY</li>
      </ol>
  <form action="" method="post" style="padding:80px;">
<b>Insert New Category:</b>
<input type="text" name="new_cat" required/>
<input type="submit" name="add_cat" value="Add Category" />
</form>
<?php
include("../includes/db_connection.php");
	if(isset($_POST['add_cat']))
  {
  	$new_cat = $_POST['new_cat'];
  	$insert_cat = "insert into category (categoryname) values ('$new_cat')";
  	$run_cat = mysqli_query($conn, $insert_cat);
  	if($run_cat)
      {
	     echo "<script>alert('New Category has been inserted!')</script>";
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