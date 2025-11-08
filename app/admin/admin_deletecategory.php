<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';

if (!isset($_GET['delete_cat'])) {
    header('Location: admin_categories.php');
    exit;
}

$categoryId = (int) $_GET['delete_cat'];

if ($categoryId <= 0) {
    header('Location: admin_categories.php');
    exit;
}

$statement = mysqli_prepare($conn, 'DELETE FROM category WHERE categoryid = ?');

if ($statement && mysqli_stmt_bind_param($statement, 'i', $categoryId) && mysqli_stmt_execute($statement)) {
    mysqli_stmt_close($statement);
    header('Location: admin_categories.php');
    exit;
}

if ($statement) {
    mysqli_stmt_close($statement);
}

header('Location: admin_categories.php?error=delete_failed');
exit;