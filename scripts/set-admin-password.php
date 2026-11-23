<?php
declare(strict_types=1);

/**
 * Set or reset an admin password, or create an admin account.
 *
 *   php scripts/set-admin-password.php <username> [<email>]
 *
 * An existing account gets the new password, and <email> too if given. A new
 * account needs <email>. Email set here counts as verified (you are at the
 * database). Uses the same DB_* environment variables as the app, so point it
 * at the database you mean to change. Prompts for the password without echoing
 * it and stores only a bcrypt hash.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../app/includes/database.php';
require_once __DIR__ . '/../app/includes/account.php';

$username = trim($argv[1] ?? '');
$email = elite_normalize_email($argv[2] ?? '');

if ($username === '') {
    fwrite(STDERR, "Usage: php scripts/set-admin-password.php <username> [<email>]\n");
    exit(1);
}

$connection = getDbConnection();
$existing = elite_find_user($connection, $username);

if ($existing === null) {
    if (($problem = elite_username_problem($username)) !== null) {
        fwrite(STDERR, $problem . "\n");
        exit(1);
    }

    if ($email === '') {
        fwrite(STDERR, "{$username} doesn't exist yet: give an email to create it.\n");
        exit(1);
    }
}

if ($email !== '') {
    if (($problem = elite_email_problem($email)) !== null) {
        fwrite(STDERR, $problem . "\n");
        exit(1);
    }

    if (elite_email_taken($connection, $email, $username)) {
        fwrite(STDERR, "That email is already used by another account.\n");
        exit(1);
    }
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

if (strlen($password) < ELITE_MIN_PASSWORD_LENGTH) {
    fwrite(STDERR, 'Use at least ' . ELITE_MIN_PASSWORD_LENGTH . " characters.\n");
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

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($existing === null) {
    elite_db_exec(
        $connection,
        'INSERT INTO users (username, password, email, email_verified_at) VALUES (?, ?, ?, UTC_TIMESTAMP())',
        'sss',
        $username,
        $hash,
        $email
    );
} else {
    elite_db_exec($connection, 'UPDATE users SET password = ? WHERE username = ?', 'ss', $hash, $username);

    if ($email !== '') {
        elite_db_exec($connection, 'UPDATE users SET email = ?, email_verified_at = UTC_TIMESTAMP() WHERE username = ?', 'ss', $email, $username);
        elite_cancel_email_changes($connection, $username);
    }
}

elite_clear_login_failures($connection, $username);
elite_cancel_reset_links($connection, $username);

fwrite(STDOUT, ($existing === null ? "Created {$username}" : "Password set for {$username}") . ' on ' . DB_HOST . '/' . DB_NAME . ".\n");
