<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/account.php';
require_once __DIR__ . '/includes/auth_page.php';

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));

    if ($username !== '') {
        elite_request_password_reset($conn, $username);
    }

    // Same answer whether or not the account exists or has an email.
    $sent = true;
}

elite_auth_page_open('Forgot password', 'Forgot your password?', 'Enter your username and we will email a reset link to the recovery address on the account.');
?>
        <?php if ($sent): ?>
            <div class="login-ok"><i class="fa-solid fa-envelope-circle-check"></i>
                If that account has a recovery email, a reset link is on its way. It works once and expires in <?php echo ELITE_RESET_TTL_MINUTES; ?> minutes.
            </div>
        <?php else: ?>
            <form method="POST" action="forgot-password.php" autocomplete="off">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <button type="submit"><i class="fa-solid fa-paper-plane"></i> Email me a reset link</button>
            </form>
        <?php endif; ?>
        <p class="login-alt">No access to that email? <a href="recover.php">Use a recovery code</a></p>
<?php elite_auth_page_close(); ?>
