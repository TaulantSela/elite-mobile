<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../LoginForm.php');
    exit;
}

require_once __DIR__ . '/../includes/db_connection.php';

/** @var mysqli $conn */
if (!isset($conn)) {
    $conn = getDbConnection();
}

