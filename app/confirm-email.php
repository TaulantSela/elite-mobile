<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/account.php';
require_once __DIR__ . '/includes/auth_page.php';

// Opening the link only shows a button: the change happens on POST, so mail
// scanners that pre-open links can't confirm it on their own.
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$change = elite_email_change_for_token($conn, $token);
$error = '';
$done = false;

if ($change !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = (string) elite_confirm_email_change($conn, $token);
    $done = $error === '';
}

elite_auth_page_open('Confirm email', 'Confirm your new email', 'The email on an Elite Mobile admin account is changing.');
?>
        <?php if ($done): ?>
            <div class="login-ok"><i class="fa-solid fa-circle-check"></i>
                Done. <strong><?php echo elite_h($change['new_email']); ?></strong> is now the email of
                <strong><?php echo elite_h($change['username']); ?></strong>. Sign in with it from now on.
            </div>
        <?php elseif ($change === null || $error !== ''): ?>
            <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i>
                <?php echo elite_h($error !== '' ? $error : 'This confirmation link is invalid, already used or expired.'); ?>
            </div>
        <?php else: ?>
            <form method="POST" action="confirm-email.php">
                <input type="hidden" name="token" value="<?php echo elite_h($token); ?>">
                <div class="field">
                    <label>Account</label>
                    <input type="text" value="<?php echo elite_h($change['username']); ?>" disabled>
                </div>
                <div class="field">
                    <label>New email</label>
                    <input type="text" value="<?php echo elite_h($change['new_email']); ?>" disabled>
                </div>
                <button type="submit"><i class="fa-solid fa-circle-check"></i> Confirm this email</button>
            </form>
        <?php endif; ?>
<?php elite_auth_page_close(); ?>
