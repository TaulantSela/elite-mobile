<?php
// Authentication helpers shared by the web-root and /admin login forms.

/**
 * Verify an admin login against the users table.
 *
 * Looks the account up with a prepared statement (no SQL injection) and checks
 * the supplied password against the stored bcrypt hash with password_verify().
 */
function verify_admin_login(mysqli $conn, string $username, string $password): bool
{
    $stmt = mysqli_prepare($conn, 'SELECT password FROM users WHERE username = ? LIMIT 1');
    if ($stmt === false) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if ($row === null || !isset($row['password'])) {
        return false;
    }

    return password_verify($password, $row['password']);
}
