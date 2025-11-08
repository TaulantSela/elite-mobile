<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

if (!isset($_GET['delete_pro'])) {
    header('Location: admin_products.php');
    exit;
}

$productId = (int) $_GET['delete_pro'];

if ($productId <= 0) {
    header('Location: admin_products.php');
    exit;
}

$statement = mysqli_prepare($conn, 'DELETE FROM product WHERE productid = ?');

if ($statement && mysqli_stmt_bind_param($statement, 'i', $productId) && mysqli_stmt_execute($statement)) {
    mysqli_stmt_close($statement);
    header('Location: admin_products.php');
    exit;
}

if ($statement) {
    mysqli_stmt_close($statement);
}

header('Location: admin_products.php?error=delete_failed');
exit;