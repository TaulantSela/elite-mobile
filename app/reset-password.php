<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/account.php';
require_once __DIR__ . '/includes/auth_page.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$link = elite_reset_token_info($conn, $token);
$username = $link['username'] ?? null;
$isInvite = ($link['purpose'] ?? '') === 'invite';
$error = '';
$done = false;

if ($username !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $problem = elite_password_problem($password, (string) ($_POST['confirm'] ?? ''));

    if ($problem !== null) {
        $error = $problem;
    } elseif (elite_consume_reset_token($conn, $token) === $username) {
        elite_set_password($conn, $username, $password);
        elite_mark_email_verified($conn, $username); // the link went to the account's email
        $done = true;
    } else {
        $username = null; // used or expired in the meantime
    }
}

elite_auth_page_open(
    $isInvite ? 'Set up your account' : 'Reset password',
    $isInvite ? 'Choose your password' : 'Choose a new password',
    ($isInvite ? 'Finish setting up your admin account. ' : '') . 'At least ' . ELITE_MIN_PASSWORD_LENGTH . ' characters.'
);
?>
        <?php if ($done): ?>
            <div class="login-ok"><i class="fa-solid fa-circle-check"></i> <?php echo $isInvite ? 'Your account is ready.' : 'Your password has been changed.'; ?> Sign in with your email or username.</div>
        <?php elseif ($username === null): ?>
            <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i>
                This reset link is invalid, already used or expired.
            </div>
            <p class="login-alt"><a href="forgot-password.php">Request a new link</a> or <a href="recover.php">use a recovery code</a>.</p>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo elite_h($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="reset-password.php" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo elite_h($token); ?>">
                <div class="field">
                    <label>Account</label>
                    <input type="text" value="<?php echo elite_h($username); ?>" disabled>
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password" autofocus>
                </div>
                <div class="field">
                    <label for="confirm">Repeat it</label>
                    <input type="password" id="confirm" name="confirm" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password">
                </div>
                <button type="submit"><i class="fa-solid fa-key"></i> Set new password</button>
            </form>
        <?php endif; ?>
<?php elite_auth_page_close(); ?>
