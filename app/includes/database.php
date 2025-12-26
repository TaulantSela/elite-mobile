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

    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASSWORD;
    $database = DB_NAME;
    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
    $socket = getenv('DB_SOCKET') ?: null;

    $connection = mysqli_init();

    if (!$connection) {
        throw new RuntimeException('Unable to initialise MySQL connection.');
    }

    $connectTimeout = (int) (getenv('DB_CONNECT_TIMEOUT') ?: 10);
    $readTimeout = (int) (getenv('DB_READ_TIMEOUT') ?: 30);

    mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, $connectTimeout);
    mysqli_options($connection, MYSQLI_OPT_READ_TIMEOUT, $readTimeout);

    $sslEnabled = filter_var(getenv('DB_SSL') ?: '0', FILTER_VALIDATE_BOOLEAN);

    if ($sslEnabled) {
        // Configure TLS when the managed database provider requires encrypted connections.
        $clientKey = getenv('DB_SSL_KEY') ?: null;
        $clientCert = getenv('DB_SSL_CERT') ?: null;
        $caCert = getenv('DB_SSL_CA') ?: null;

        if (!mysqli_ssl_set($connection, $clientKey ?: null, $clientCert ?: null, $caCert ?: null, null, null)) {
            throw new RuntimeException('Failed to configure MySQL SSL options: ' . mysqli_error($connection));
        }

        $flags = MYSQLI_CLIENT_SSL;

        if (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT')) {
            $flags |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }

        if (!mysqli_real_connect($connection, $host, $user, $password, $database, $port, $socket ?: null, $flags)) {
            throw new RuntimeException('Could not establish MySQL SSL connection: ' . mysqli_connect_error());
        }
    } else {
        if (!mysqli_real_connect($connection, $host, $user, $password, $database, $port, $socket ?: null)) {
            throw new RuntimeException('Could not connect to MySQL: ' . mysqli_connect_error());
        }
    }

    mysqli_set_charset($connection, 'utf8mb4');

    return $connection;
}

