<?php 

	include("db_connection.php"); 
	
	if(isset($_GET['delete_pro'])){
	
	$delete_id = $_GET['delete_pro'];
	
	$delete_pro = "delete from product where productid='$delete_id'"; 
	
	$run_delete = mysqli_query($conn, $delete_pro); 
	
	if($run_delete){
	
	echo "<script>alert('A product has been deleted!')</script>";
	echo "<script>window.open('admin_products.php','_self')</script>";
	}
	}
?>