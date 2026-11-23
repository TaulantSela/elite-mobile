<?php

declare(strict_types=1);

/**
 * Admin authentication helpers.
 *
 * Passwords are stored as bcrypt hashes (PASSWORD_DEFAULT). Any legacy plaintext
 * credential still in the database is accepted once with a constant-time compare
 * and immediately rehashed on successful login, so the store migrates itself
 * without downtime and no plaintext survives a first sign-in.
 */

/**
 * Hashes of the sample admin passwords that were published in this repo (in an
 * old README and in earlier versions of phpproject.sql, once as plaintext).
 * Whatever the database holds, a login with one of those passwords is refused:
 * set a new one with scripts/set-admin-password.php.
 */
const ELITE_PUBLISHED_PASSWORD_HASHES = [
    '$2y$12$N614K64naAd6XnleNh6e3e487xZWOkk0LhYiepupNE4eJHeMoUI1.',
    '$2y$12$SnYTO8.gEeiTPMoYbXzKe.fnXtKSpnXDIj1ZqOz.cKDRHMwEkOYKm',
];

/** Is this one of the passwords published in the repository? */
function elite_password_is_published(string $password): bool
{
    foreach (ELITE_PUBLISHED_PASSWORD_HASHES as $hash) {
        if (password_verify($password, $hash)) {
            return true;
        }
    }

    return false;
}

/** Does the stored value look like a modern password hash (bcrypt / argon2)? */
function elite_password_is_hashed(string $stored): bool
{
    return (bool) preg_match('/^\$(2[aby]|argon2)/', $stored);
}

/** Persist a freshly computed hash for a user (best-effort). */
function elite_store_password_hash(mysqli $conn, string $username, string $plainPassword): void
{
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    if (!is_string($hash) || $hash === '') {
        return;
    }

    $statement = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE username = ?');

    if ($statement) {
        mysqli_stmt_bind_param($statement, 'ss', $hash, $username);
        @mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
    }
}

/**
 * Verify a login attempt. Returns true on success and transparently upgrades
 * legacy plaintext passwords (and outdated hashes) to the current algorithm.
 */
function elite_verify_login(mysqli $conn, string $username, string $password): bool
{
    if (elite_password_is_published($password)) {
        return false;
    }

    $statement = mysqli_prepare($conn, 'SELECT password FROM users WHERE username = ? LIMIT 1');

    if (!$statement) {
        return false;
    }

    mysqli_stmt_bind_param($statement, 's', $username);

    if (!mysqli_stmt_execute($statement)) {
        mysqli_stmt_close($statement);

        return false;
    }

    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($result) {
        mysqli_free_result($result);
    }

    mysqli_stmt_close($statement);

    if ($row === null) {
        return false;
    }

    $stored = (string) $row['password'];

    if (elite_password_is_hashed($stored)) {
        if (!password_verify($password, $stored)) {
            return false;
        }

        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            elite_store_password_hash($conn, $username, $password);
        }

        return true;
    }

    // Legacy plaintext password: constant-time compare, then migrate to a hash.
    if (hash_equals($stored, $password)) {
        elite_store_password_hash($conn, $username, $password);

        return true;
    }

    return false;
}
