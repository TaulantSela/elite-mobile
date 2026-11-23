<?php
declare(strict_types=1);

/**
 * Set or reset an admin password (creates the user if it doesn't exist).
 *
 *   php scripts/set-admin-password.php <username>
 *
 * Uses the same DB_* environment variables as the app, so point it at the
 * database you mean to change. Prompts for the password without echoing it and
 * stores only a bcrypt hash.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../app/includes/database.php';
require_once __DIR__ . '/../app/includes/auth.php';

$username = trim($argv[1] ?? '');

if ($username === '') {
    fwrite(STDERR, "Usage: php scripts/set-admin-password.php <username>\n");
    exit(1);
}

$readHidden = static function (string $prompt): string {
    fwrite(STDOUT, $prompt);
    $isTty = stream_isatty(STDIN);

    if ($isTty) {
        system('stty -echo');
    }

    $line = fgets(STDIN);

    if ($isTty) {
        system('stty echo');
        fwrite(STDOUT, "\n");
    }

    return rtrim((string) $line, "\r\n");
};

$password = $readHidden("New password for {$username}: ");

if (strlen($password) < 12) {
    fwrite(STDERR, "Use at least 12 characters.\n");
    exit(1);
}

if (elite_password_is_published($password)) {
    fwrite(STDERR, "That password was published in this repository; choose another.\n");
    exit(1);
}

if ($readHidden('Repeat it: ') !== $password) {
    fwrite(STDERR, "The passwords don't match.\n");
    exit(1);
}

$connection = getDbConnection();
$hash = password_hash($password, PASSWORD_DEFAULT);
$statement = mysqli_prepare(
    $connection,
    'INSERT INTO users (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)'
);
mysqli_stmt_bind_param($statement, 'ss', $username, $hash);
mysqli_stmt_execute($statement);
mysqli_stmt_close($statement);

fwrite(STDOUT, "Password set for {$username} on " . DB_HOST . '/' . DB_NAME . ".\n");
