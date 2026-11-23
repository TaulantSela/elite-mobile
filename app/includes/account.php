<?php
declare(strict_types=1);

/**
 * Admin accounts: emails, password rules, recovery codes, emailed reset and
 * invite links, and verified email changes.
 *
 * Every admin account has a unique email. It is used to sign in (as is the
 * username) and for reset links, and it only changes once the new address is
 * confirmed through a link sent to it.
 *
 * Secrets are stored only as hashes: passwords and recovery codes with
 * password_hash(), link tokens as SHA-256 (long random values, so a fast hash is
 * enough and lets us look them up). Reset links last 30 minutes, invites 72
 * hours and email confirmations 24 hours. Each link works once, and at most
 * three of a kind can be sent per account per hour.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/url_helpers.php';
require_once __DIR__ . '/throttle.php';

const ELITE_MIN_PASSWORD_LENGTH = 8;
const ELITE_RESET_TTL_MINUTES = 30;
const ELITE_INVITE_TTL_MINUTES = 4320;        // 72 hours
const ELITE_EMAIL_CHANGE_TTL_MINUTES = 1440;  // 24 hours
const ELITE_RESET_MAX_PER_HOUR = 3;

/* ---------------------------------------------------------------------
 * Small query helpers (prepared statements only).
 * ------------------------------------------------------------------- */

/** @return array<string, mixed>|null The first row, or null. */
function elite_db_row(mysqli $conn, string $sql, string $types = '', mixed ...$params): ?array
{
    $statement = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($statement, $types, ...$params);
    }
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);

    return $row ?: null;
}

/** @return list<array<string, mixed>> */
function elite_db_rows(mysqli $conn, string $sql, string $types = '', mixed ...$params): array
{
    $statement = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($statement, $types, ...$params);
    }
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($statement);

    return $rows;
}

/** Run a write; returns the number of affected rows. */
function elite_db_exec(mysqli $conn, string $sql, string $types = '', mixed ...$params): int
{
    $statement = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($statement, $types, ...$params);
    }
    mysqli_stmt_execute($statement);
    $affected = (int) mysqli_stmt_affected_rows($statement);
    mysqli_stmt_close($statement);

    return $affected;
}

function elite_h_mail(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------------------
 * Passwords.
 * ------------------------------------------------------------------- */

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
    elite_db_exec($conn, 'UPDATE users SET password = ? WHERE username = ?', 'ss', $hash, $username);

    // A new password clears the sign-in lock, so a reset always gets the owner back in.
    elite_clear_login_failures($conn, $username);
}

/* ---------------------------------------------------------------------
 * Accounts and their emails.
 * ------------------------------------------------------------------- */

/** Usernames never contain "@", so sign-in can tell them apart from emails. */
function elite_username_problem(string $username): ?string
{
    return preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username) === 1
        ? null
        : 'A username is 3 to 50 letters, digits, dots, dashes or underscores.';
}

function elite_normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function elite_email_problem(string $email): ?string
{
    if ($email === '' || strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return 'Enter a valid email address.';
    }

    return null;
}

/** Is this already the email of an account other than $exceptUsername? */
function elite_email_taken(mysqli $conn, string $email, string $exceptUsername = ''): bool
{
    return elite_db_row($conn, 'SELECT 1 AS hit FROM users WHERE email = ? AND username <> ? LIMIT 1', 'ss', $email, $exceptUsername) !== null;
}

/**
 * @return array{username:string, email:string, email_verified:bool, has_recovery_code:bool, pending_email:?string}|null
 */
function elite_find_user(mysqli $conn, string $username): ?array
{
    $row = elite_db_row($conn, 'SELECT username, email, email_verified_at, recovery_code FROM users WHERE username = ? LIMIT 1', 's', $username);

    if ($row === null) {
        return null;
    }

    return [
        'username' => (string) $row['username'],
        'email' => (string) $row['email'],
        'email_verified' => $row['email_verified_at'] !== null,
        'has_recovery_code' => (string) ($row['recovery_code'] ?? '') !== '',
        'pending_email' => elite_pending_email($conn, (string) $row['username']),
    ];
}

/** The account a sign-in identifier belongs to: an email if it contains "@", otherwise a username. */
function elite_resolve_login(mysqli $conn, string $identifier): ?string
{
    $identifier = trim($identifier);

    if ($identifier === '') {
        return null;
    }

    $row = str_contains($identifier, '@')
        ? elite_db_row($conn, 'SELECT username FROM users WHERE email = ? LIMIT 1', 's', elite_normalize_email($identifier))
        : elite_db_row($conn, 'SELECT username FROM users WHERE username = ? LIMIT 1', 's', $identifier);

    return $row !== null ? (string) $row['username'] : null;
}

/** @return list<array{username:string, email:string, email_verified:bool, pending_email:?string}> */
function elite_list_admins(mysqli $conn): array
{
    $pending = [];
    foreach (elite_db_rows($conn, 'SELECT username, new_email FROM email_changes WHERE used_at IS NULL AND expires_at > UTC_TIMESTAMP() ORDER BY id') as $row) {
        $pending[(string) $row['username']] = (string) $row['new_email'];
    }

    $admins = [];
    foreach (elite_db_rows($conn, 'SELECT username, email, email_verified_at FROM users ORDER BY username') as $row) {
        $admins[] = [
            'username' => (string) $row['username'],
            'email' => (string) $row['email'],
            'email_verified' => $row['email_verified_at'] !== null,
            'pending_email' => $pending[(string) $row['username']] ?? null,
        ];
    }

    return $admins;
}

/** The account's address is proven (a link sent to it was used). */
function elite_mark_email_verified(mysqli $conn, string $username): void
{
    elite_db_exec($conn, 'UPDATE users SET email_verified_at = UTC_TIMESTAMP() WHERE username = ? AND email_verified_at IS NULL', 's', $username);
}

/**
 * Create an admin with no usable password and email them an invite link to set
 * one. Returns an error message, or null on success.
 */
function elite_create_admin(mysqli $conn, string $username, string $email, string $invitedBy): ?string
{
    $username = trim($username);
    $email = elite_normalize_email($email);
    $problem = elite_username_problem($username) ?? elite_email_problem($email);

    if ($problem !== null) {
        return $problem;
    }

    if (elite_find_user($conn, $username) !== null) {
        return 'That username is already taken.';
    }

    if (elite_email_taken($conn, $email)) {
        return 'That email is already used by another account.';
    }

    // A hash of a random value nobody knows: the account can't sign in until the invite is used.
    $unusable = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    try {
        elite_db_exec($conn, 'INSERT INTO users (username, password, email) VALUES (?, ?, ?)', 'sss', $username, $unusable, $email);
    } catch (mysqli_sql_exception) {
        return 'That username or email was just taken. Try again.';
    }

    elite_send_invite($conn, $username, $invitedBy);

    return null;
}

/** Remove an admin account and everything tied to it. */
function elite_delete_admin(mysqli $conn, string $username): void
{
    foreach (['password_resets', 'email_changes', 'login_attempts', 'users'] as $table) {
        elite_db_exec($conn, "DELETE FROM {$table} WHERE username = ?", 's', $username);
    }
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

    elite_db_exec($conn, 'UPDATE users SET recovery_code = ? WHERE username = ?', 'ss', password_hash($raw, PASSWORD_DEFAULT), $username);

    return implode('-', str_split($raw, 5));
}

function elite_recovery_code_matches(mysqli $conn, string $username, string $code): bool
{
    $row = elite_db_row($conn, 'SELECT recovery_code FROM users WHERE username = ? LIMIT 1', 's', $username);
    $stored = (string) ($row['recovery_code'] ?? '');

    if ($stored === '') {
        // Spend the same time as a real check so timing doesn't reveal accounts without a code.
        password_verify('x', ELITE_PUBLISHED_PASSWORD_HASHES[0]);

        return false;
    }

    return password_verify(elite_normalize_recovery_code($code), $stored);
}

/* ---------------------------------------------------------------------
 * Emailed links: password resets and invites share password_resets.
 * ------------------------------------------------------------------- */

/** @return array{0:string, 1:string} [token for the link, SHA-256 to store] */
function elite_new_token(): array
{
    $token = bin2hex(random_bytes(32));

    return [$token, hash('sha256', $token)];
}

function elite_links_sent_last_hour(mysqli $conn, string $table, string $username): int
{
    $row = elite_db_row($conn, "SELECT COUNT(*) AS n FROM {$table} WHERE username = ? AND created_at > UTC_TIMESTAMP() - INTERVAL 1 HOUR", 's', $username);

    return (int) ($row['n'] ?? 0);
}

function elite_create_password_link(mysqli $conn, string $username, string $purpose, int $ttlMinutes): string
{
    [$token, $tokenHash] = elite_new_token();
    elite_db_exec(
        $conn,
        "INSERT INTO password_resets (username, token_hash, purpose, expires_at, created_at)
         VALUES (?, ?, ?, UTC_TIMESTAMP() + INTERVAL {$ttlMinutes} MINUTE, UTC_TIMESTAMP())",
        'sss',
        $username,
        $tokenHash,
        $purpose
    );

    return $token;
}

/**
 * Email a reset link if the account exists and isn't over the hourly limit.
 * Callers always show the same message, so the response never reveals which.
 */
function elite_request_password_reset(mysqli $conn, string $username): void
{
    $user = elite_find_user($conn, $username);

    if ($user === null || elite_links_sent_last_hour($conn, 'password_resets', $username) >= ELITE_RESET_MAX_PER_HOUR) {
        return;
    }

    $ttl = ELITE_RESET_TTL_MINUTES;
    $link = elite_public_url('reset-password.php?token=' . elite_create_password_link($conn, $username, 'reset', $ttl));
    $text = "Someone asked to reset the password for the Elite Mobile admin account \"{$username}\".\n\n"
        . "Choose a new password here (the link works once and expires in {$ttl} minutes):\n{$link}\n\n"
        . "If this wasn't you, ignore this email: your password stays the same.";
    $html = '<p>Someone asked to reset the password for the Elite Mobile admin account <strong>' . elite_h_mail($username) . '</strong>.</p>'
        . '<p><a href="' . elite_h_mail($link) . "\">Choose a new password</a>. The link works once and expires in {$ttl} minutes.</p>"
        . "<p>If this wasn't you, ignore this email: your password stays the same.</p>";

    elite_send_mail($user['email'], 'Reset your Elite Mobile admin password', $text, $html, $link);
}

/** Email a fresh invite link (72 hours) for an account that hasn't set its password yet. */
function elite_send_invite(mysqli $conn, string $username, string $invitedBy): bool
{
    $user = elite_find_user($conn, $username);

    if ($user === null || $user['email_verified'] || elite_links_sent_last_hour($conn, 'password_resets', $username) >= ELITE_RESET_MAX_PER_HOUR) {
        return false;
    }

    elite_cancel_reset_links($conn, $username); // only the newest invite works
    $link = elite_public_url('reset-password.php?token=' . elite_create_password_link($conn, $username, 'invite', ELITE_INVITE_TTL_MINUTES));
    $text = "{$invitedBy} added you as an admin of Elite Mobile.\n\n"
        . "Your username is \"{$username}\"; you can also sign in with this email.\n"
        . "Choose your password here (the link works once and expires in 72 hours):\n{$link}";
    $html = '<p>' . elite_h_mail($invitedBy) . ' added you as an admin of Elite Mobile.</p>'
        . '<p>Your username is <strong>' . elite_h_mail($username) . '</strong>; you can also sign in with this email.</p>'
        . '<p><a href="' . elite_h_mail($link) . '">Choose your password</a>. The link works once and expires in 72 hours.</p>';

    elite_send_mail($user['email'], "You've been added as an Elite Mobile admin", $text, $html, $link);

    return true;
}

/** @return array{username:string, purpose:string}|null For an unused, unexpired reset or invite token. */
function elite_reset_token_info(mysqli $conn, string $token): ?array
{
    if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
        return null;
    }

    $row = elite_db_row(
        $conn,
        'SELECT username, purpose FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() LIMIT 1',
        's',
        hash('sha256', $token)
    );

    return $row !== null ? ['username' => (string) $row['username'], 'purpose' => (string) $row['purpose']] : null;
}

/**
 * Use up a reset or invite token (atomically, so it can't be used twice) and
 * every other open link for the same account. Returns the username, or null if
 * the token was no longer valid.
 */
function elite_consume_reset_token(mysqli $conn, string $token): ?string
{
    $info = elite_reset_token_info($conn, $token);

    if ($info === null) {
        return null;
    }

    $claimed = elite_db_exec(
        $conn,
        'UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP()',
        's',
        hash('sha256', $token)
    ) === 1;

    if (!$claimed) {
        return null;
    }

    elite_cancel_reset_links($conn, $info['username']);

    return $info['username'];
}

/** Invalidate all open reset and invite links for an account. */
function elite_cancel_reset_links(mysqli $conn, string $username): void
{
    elite_db_exec($conn, 'UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE username = ? AND used_at IS NULL', 's', $username);
}

/* ---------------------------------------------------------------------
 * Email changes: the new address takes effect only after the link sent to
 * it is used, and the current address gets a heads-up.
 * ------------------------------------------------------------------- */

function elite_pending_email(mysqli $conn, string $username): ?string
{
    $row = elite_db_row(
        $conn,
        'SELECT new_email FROM email_changes WHERE username = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() ORDER BY id DESC LIMIT 1',
        's',
        $username
    );

    return $row !== null ? (string) $row['new_email'] : null;
}

/** Returns an error message, or null when the confirmation link was sent. */
function elite_request_email_change(mysqli $conn, string $username, string $newEmail): ?string
{
    $user = elite_find_user($conn, $username);
    $newEmail = elite_normalize_email($newEmail);

    if ($user === null) {
        return 'That account no longer exists.';
    }

    $problem = elite_email_problem($newEmail);

    if ($problem !== null) {
        return $problem;
    }

    if ($newEmail === $user['email']) {
        return 'That is already the email on this account.';
    }

    if (elite_email_taken($conn, $newEmail, $username)) {
        return 'That email is already used by another account.';
    }

    if (elite_links_sent_last_hour($conn, 'email_changes', $username) >= ELITE_RESET_MAX_PER_HOUR) {
        return 'Too many email changes in the last hour. Try again later.';
    }

    elite_cancel_email_changes($conn, $username);
    [$token, $tokenHash] = elite_new_token();
    $ttl = ELITE_EMAIL_CHANGE_TTL_MINUTES;
    elite_db_exec(
        $conn,
        "INSERT INTO email_changes (username, new_email, token_hash, expires_at, created_at)
         VALUES (?, ?, ?, UTC_TIMESTAMP() + INTERVAL {$ttl} MINUTE, UTC_TIMESTAMP())",
        'sss',
        $username,
        $newEmail,
        $tokenHash
    );

    $link = elite_public_url('confirm-email.php?token=' . $token);
    $sent = elite_send_mail(
        $newEmail,
        'Confirm your new Elite Mobile email',
        "Confirm that this address should become the email of the Elite Mobile admin account \"{$username}\":\n{$link}\n\n"
            . "The link works once and expires in 24 hours. If you didn't expect this, ignore it: nothing changes.",
        '<p>Confirm that this address should become the email of the Elite Mobile admin account <strong>' . elite_h_mail($username) . '</strong>.</p>'
            . '<p><a href="' . elite_h_mail($link) . '">Confirm this email</a>. The link works once and expires in 24 hours.</p>'
            . "<p>If you didn't expect this, ignore it: nothing changes.</p>",
        $link
    );

    elite_send_mail(
        $user['email'],
        'Your Elite Mobile email is being changed',
        "Someone asked to change the email of the Elite Mobile admin account \"{$username}\" to {$newEmail}.\n\n"
            . "Nothing changes unless the link sent to that address is used. If this wasn't you, sign in and change your password.",
        '<p>Someone asked to change the email of the Elite Mobile admin account <strong>' . elite_h_mail($username) . '</strong> to '
            . elite_h_mail($newEmail) . '.</p>'
            . "<p>Nothing changes unless the link sent to that address is used. If this wasn't you, sign in and change your password.</p>"
    );

    if (!$sent && getenv('VERCEL')) {
        elite_cancel_email_changes($conn, $username);

        return 'The confirmation email could not be sent. Try again later.';
    }

    return null;
}

/** @return array{username:string, new_email:string}|null For an unused, unexpired confirmation token. */
function elite_email_change_for_token(mysqli $conn, string $token): ?array
{
    if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
        return null;
    }

    $row = elite_db_row(
        $conn,
        'SELECT username, new_email FROM email_changes WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() LIMIT 1',
        's',
        hash('sha256', $token)
    );

    return $row !== null ? ['username' => (string) $row['username'], 'new_email' => (string) $row['new_email']] : null;
}

/** Apply a confirmed email change. Returns an error message, or null on success. */
function elite_confirm_email_change(mysqli $conn, string $token): ?string
{
    $change = elite_email_change_for_token($conn, $token);
    $invalid = 'This confirmation link is invalid, already used or expired.';

    if ($change === null) {
        return $invalid;
    }

    $claimed = elite_db_exec(
        $conn,
        'UPDATE email_changes SET used_at = UTC_TIMESTAMP() WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP()',
        's',
        hash('sha256', $token)
    ) === 1;

    if (!$claimed) {
        return $invalid;
    }

    if (elite_email_taken($conn, $change['new_email'], $change['username'])) {
        return 'That email is now used by another account, so it was not changed.';
    }

    try {
        elite_db_exec($conn, 'UPDATE users SET email = ?, email_verified_at = UTC_TIMESTAMP() WHERE username = ?', 'ss', $change['new_email'], $change['username']);
    } catch (mysqli_sql_exception) {
        return 'That email is now used by another account, so it was not changed.';
    }

    elite_cancel_email_changes($conn, $change['username']);
    elite_cancel_reset_links($conn, $change['username']); // links sent to the old address stop working

    return null;
}

function elite_cancel_email_changes(mysqli $conn, string $username): void
{
    elite_db_exec($conn, 'UPDATE email_changes SET used_at = UTC_TIMESTAMP() WHERE username = ? AND used_at IS NULL', 's', $username);
}
