<?php
require_once __DIR__ . '/includes/admin_guard.php';
include("db_connection.php");

if (isset($_GET['delete_cat'])) {
	$delete_id = (int) $_GET['delete_cat'];

	$stmt = mysqli_prepare($conn, "DELETE FROM category WHERE categoryid = ?");
	mysqli_stmt_bind_param($stmt, "i", $delete_id);
	$run_delete = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

	if ($run_delete) {
		echo "<script>alert('A Category has been deleted!')</script>";
		echo "<script>window.open('admin_categories.php','_self')</script>";
	}
}
?>
