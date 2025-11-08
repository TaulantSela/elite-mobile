<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Returns a shared mysqli connection instance.
 */
function getDbConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
    $socket = getenv('DB_SOCKET') ?: null;

    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket ?: null);

    if (!$connection) {
        throw new RuntimeException('Could not connect to MySQL: ' . mysqli_connect_error());
    }

    mysqli_set_charset($connection, 'utf8mb4');

    return $connection;
}

