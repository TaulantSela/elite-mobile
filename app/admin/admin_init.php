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

// An admin who has been removed is signed out on their next request.
$sessionCheck = mysqli_prepare($conn, 'SELECT 1 FROM users WHERE username = ? LIMIT 1');
$sessionUsername = (string) $_SESSION['username'];
mysqli_stmt_bind_param($sessionCheck, 's', $sessionUsername);
mysqli_stmt_execute($sessionCheck);
$sessionUserExists = mysqli_fetch_row(mysqli_stmt_get_result($sessionCheck)) !== null;
mysqli_stmt_close($sessionCheck);

if (!$sessionUserExists) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../LoginForm.php');
    exit;
}

