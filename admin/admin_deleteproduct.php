<?php
require_once __DIR__ . '/../includes/admin_guard.php';
include("../includes/db_connection.php");

if (isset($_GET['delete_pro'])) {
	$delete_id = (int) $_GET['delete_pro'];

	$stmt = mysqli_prepare($conn, "DELETE FROM product WHERE productid = ?");
	mysqli_stmt_bind_param($stmt, "i", $delete_id);
	$run_delete = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

	if ($run_delete) {
		echo "<script>alert('A product has been deleted!')</script>";
		echo "<script>window.open('admin_products.php','_self')</script>";
	}
}
?>
