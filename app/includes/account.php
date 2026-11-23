<?php
declare(strict_types=1);

/**
 * Admin account helpers: password rules, recovery email, recovery codes and
 * emailed reset links.
 *
 * Secrets are stored only as hashes: passwords and recovery codes with
 * password_hash(), reset tokens as SHA-256 (they are long random values, so a
 * fast hash is enough and lets us look them up). Reset links last 30 minutes,
 * work once, and at most three can be requested per account per hour.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/url_helpers.php';

const ELITE_MIN_PASSWORD_LENGTH = 12;
const ELITE_RESET_TTL_MINUTES = 30;
const ELITE_RESET_MAX_PER_HOUR = 3;

/** Why a new password can't be used, or null if it can. */
function elite_password_problem(string $password, string $confirm): ?string
{
    if (strlen($password) < ELITE_MIN_PASSWORD_LENGTH) {
        return 'Use at least ' . ELITE_MIN_PASSWORD_LENGTH . ' characters.';
    }

    if (!hash_equals($password, $confirm)) {
        return "The two passwords don't match.";
    }

    if (elite_password_is_published($password)) {
        return 'That password was published with this project; choose another.';
    }

    return null;
}

/** Store a new password (as a bcrypt hash) for a user. */
function elite_set_password(mysqli $conn, string $username, string $password): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $statement = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE username = ?');
    mysqli_stmt_bind_param($statement, 'ss', $hash, $username);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

/** @return array{username:string, email:?string, has_recovery_code:bool}|null */
function elite_find_user(mysqli $conn, string $username): ?array
{
    $statement = mysqli_prepare($conn, 'SELECT username, email, recovery_code FROM users WHERE username = ? LIMIT 1');
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);

    if (!$row) {
        return null;
    }

    return [
        'username' => (string) $row['username'],
        'email' => $row['email'] !== null && $row['email'] !== '' ? (string) $row['email'] : null,
        'has_recovery_code' => $row['recovery_code'] !== null && $row['recovery_code'] !== '',
    ];
}

function elite_set_email(mysqli $conn, string $username, ?string $email): void
{
    $statement = mysqli_prepare($conn, 'UPDATE users SET email = ? WHERE username = ?');
    mysqli_stmt_bind_param($statement, 'ss', $email, $username);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

/* ---------------------------------------------------------------------
 * Recovery codes: 20 random characters (100 bits), shown once as
 * XXXXX-XXXXX-XXXXX-XXXXX. Using one to reset replaces it with a new one.
 * ------------------------------------------------------------------- */

function elite_normalize_recovery_code(string $code): string
{
    return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
}

/** Create a new recovery code for a user, replacing any old one, and return it. */
function elite_new_recovery_code(mysqli $conn, string $username): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O or 1/I
    $raw = '';
    for ($i = 0; $i < 20; $i++) {
        $raw .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    $hash = password_hash($raw, PASSWORD_DEFAULT);
    $statement = mysqli_prepare($conn, 'UPDATE users SET recovery_code = ? WHERE username = ?');
    mysqli_stmt_bind_param($statement, 'ss', $hash, $username);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);

    return implode('-', str_split($raw, 5));
}

function elite_recovery_code_matches(mysqli $conn, string $username, string $code): bool
{
    $statement = mysqli_prepare($conn, 'SELECT recovery_code FROM users WHERE username = ? LIMIT 1');
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);

    $stored = (string) ($row['recovery_code'] ?? '');

    if ($stored === '') {
        // Spend the same time as a real check so timing doesn't reveal accounts without a code.
        password_verify('x', ELITE_PUBLISHED_PASSWORD_HASHES[0]);

        return false;
    }

    return password_verify(elite_normalize_recovery_code($code), $stored);
}

/* ---------------------------------------------------------------------
 * Emailed reset links.
 * ------------------------------------------------------------------- */

/**
 * Email a reset link if the account exists, has an email and isn't over the
 * hourly limit. Callers always show the same message, so the response never
 * reveals which of those was true.
 */
function elite_request_password_reset(mysqli $conn, string $username): void
{
    $user = elite_find_user($conn, $username);

    if ($user === null || $user['email'] === null) {
        return;
    }

    $statement = mysqli_prepare(
        $conn,
        'SELECT COUNT(*) FROM password_resets WHERE username = ? AND created_at > UTC_TIMESTAMP() - INTERVAL 1 HOUR'
    );
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $recent = (int) (mysqli_fetch_row(mysqli_stmt_get_result($statement))[0] ?? 0);
    mysqli_stmt_close($statement);

    if ($recent >= ELITE_RESET_MAX_PER_HOUR) {
        return;
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $ttl = ELITE_RESET_TTL_MINUTES;
    $statement = mysqli_prepare(
        $conn,
        "INSERT INTO password_resets (username, token_hash, expires_at, created_at)
         VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL {$ttl} MINUTE, UTC_TIMESTAMP())"
    );
    mysqli_stmt_bind_param($statement, 'ss', $username, $tokenHash);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);

    $link = elite_public_url('reset-password.php?token=' . $token);
    $text = "Someone asked to reset the password for the Elite Mobile admin account \"{$username}\".\n\n"
        . "Choose a new password here (the link works once and expires in {$ttl} minutes):\n{$link}\n\n"
        . "If this wasn't you, ignore this email: your password stays the same.";
    $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $html = "<p>Someone asked to reset the password for the Elite Mobile admin account <strong>{$safeUser}</strong>.</p>"
        . "<p><a href=\"{$safeLink}\">Choose a new password</a>. The link works once and expires in {$ttl} minutes.</p>"
        . "<p>If this wasn't you, ignore this email: your password stays the same.</p>";

    elite_send_mail($user['email'], 'Reset your Elite Mobile admin password', $text, $html, $link);
}

/** The username a reset token belongs to, if it is unused and unexpired. */
function elite_reset_token_user(mysqli $conn, string $token): ?string
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }

    $tokenHash = hash('sha256', $token);
    $statement = mysqli_prepare(
        $conn,
        'SELECT username FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() LIMIT 1'
    );
    mysqli_stmt_bind_param($statement, 's', $tokenHash);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);

    return $row ? (string) $row['username'] : null;
}

/**
 * Use up a reset token (atomically, so it can't be used twice) and every other
 * open token for the same account. Returns the username, or null if the token
 * was no longer valid.
 */
function elite_consume_reset_token(mysqli $conn, string $token): ?string
{
    $username = elite_reset_token_user($conn, $token);

    if ($username === null) {
        return null;
    }

    $tokenHash = hash('sha256', $token);
    $statement = mysqli_prepare(
        $conn,
        'UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP()'
    );
    mysqli_stmt_bind_param($statement, 's', $tokenHash);
    mysqli_stmt_execute($statement);
    $claimed = mysqli_stmt_affected_rows($statement) === 1;
    mysqli_stmt_close($statement);

    if (!$claimed) {
        return null;
    }

    elite_cancel_reset_links($conn, $username);

    return $username;
}

/** Invalidate all open reset links for an account (after any password change). */
function elite_cancel_reset_links(mysqli $conn, string $username): void
{
    $statement = mysqli_prepare($conn, 'UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE username = ? AND used_at IS NULL');
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}
