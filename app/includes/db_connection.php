<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

try {
    $conn = getDbConnection();
} catch (RuntimeException $exception) {
    echo $exception->getMessage();
    exit;
}