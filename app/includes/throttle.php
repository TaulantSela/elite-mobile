<?php
declare(strict_types=1);

/**
 * Sign-in throttling. Failed attempts are stored in login_attempts with a hash
 * of the client IP (never the IP itself) and kept for a day.
 *
 * - 5 failures for one username within 15 minutes lock that username for 15
 *   minutes, even with the right password.
 * - 20 failures from one IP within 15 minutes lock that IP out of sign-in.
 *
 * A successful sign-in or password reset clears the username's failures, so
 * the owner can always get back in through "Forgot password?".
 */

const ELITE_LOCK_WINDOW_MINUTES = 15;
const ELITE_LOCK_MAX_PER_USER = 5;
const ELITE_LOCK_MAX_PER_IP = 20;

/** SHA-256 of the client IP. On Vercel the first X-Forwarded-For entry is the client. */
function elite_client_ip_hash(): string
{
    $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    $ip = $forwarded !== '' ? trim(explode(',', $forwarded)[0]) : (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    return hash('sha256', $ip);
}

/** Minutes left on the lock for this column/value, or 0 if not locked. */
function elite_lock_minutes_left(mysqli $conn, string $column, string $value, int $max): int
{
    $window = ELITE_LOCK_WINDOW_MINUTES;
    $statement = mysqli_prepare(
        $conn,
        "SELECT COUNT(*), TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), MAX(attempted_at) + INTERVAL {$window} MINUTE)
         FROM login_attempts WHERE {$column} = ? AND attempted_at > UTC_TIMESTAMP() - INTERVAL {$window} MINUTE"
    );
    mysqli_stmt_bind_param($statement, 's', $value);
    mysqli_stmt_execute($statement);
    $row = mysqli_fetch_row(mysqli_stmt_get_result($statement)) ?: [0, 0];
    mysqli_stmt_close($statement);

    if ((int) $row[0] < $max) {
        return 0;
    }

    return max(1, (int) ceil(((int) $row[1]) / 60));
}

/** Minutes until this username may try to sign in again (0 = allowed now). */
function elite_login_lock_minutes(mysqli $conn, string $username): int
{
    return max(
        elite_lock_minutes_left($conn, 'username', $username, ELITE_LOCK_MAX_PER_USER),
        elite_lock_minutes_left($conn, 'ip_hash', elite_client_ip_hash(), ELITE_LOCK_MAX_PER_IP)
    );
}

function elite_record_login_failure(mysqli $conn, string $username): void
{
    $ipHash = elite_client_ip_hash();
    $username = substr($username, 0, 50);
    $statement = mysqli_prepare($conn, 'INSERT INTO login_attempts (username, ip_hash, attempted_at) VALUES (?, ?, UTC_TIMESTAMP())');
    mysqli_stmt_bind_param($statement, 'ss', $username, $ipHash);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);

    mysqli_query($conn, 'DELETE FROM login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');
}

function elite_clear_login_failures(mysqli $conn, string $username): void
{
    $statement = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE username = ?');
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function elite_lock_message(int $minutes): string
{
    return 'Too many failed attempts. Try again in ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's')
        . ', or reset your password.';
}
