<?php
	include("../includes/db_connection.php");
	if(isset($_GET['delete_cat'])){
	    $delete_id = $_GET['delete_cat'];
	    $delete_cat = "delete from category where categoryid='$delete_id'";
	    $run_delete = mysqli_query($conn, $delete_cat);
	    if($run_delete){
	        echo "<script>alert('A Category has been deleted!')</script>";
	        echo "<script>window.open('admin_categories.php','_self')</script>";
	    }
	}
?>