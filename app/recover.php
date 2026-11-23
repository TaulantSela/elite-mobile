<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/account.php';
require_once __DIR__ . '/includes/auth_page.php';

$error = '';
$newCode = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $problem = elite_password_problem($password, (string) ($_POST['confirm'] ?? ''));

    if ($username === '' || !elite_recovery_code_matches($conn, $username, (string) ($_POST['code'] ?? ''))) {
        usleep(random_int(200000, 400000));
        $error = "That username and recovery code don't match.";
    } elseif ($problem !== null) {
        $error = $problem;
    } else {
        elite_set_password($conn, $username, $password);
        elite_cancel_reset_links($conn, $username);
        $newCode = elite_new_recovery_code($conn, $username); // the old code is now spent
    }
}

elite_auth_page_open('Recover account', 'Use a recovery code', 'Your username, the recovery code you saved, and a new password.');
?>
        <?php if ($newCode !== null): ?>
            <div class="login-ok"><i class="fa-solid fa-circle-check"></i> Your password has been changed and your old recovery code no longer works.</div>
            <?php elite_render_recovery_code($newCode); ?>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo elite_h($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="recover.php" autocomplete="off">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo elite_h((string) ($_POST['username'] ?? '')); ?>">
                </div>
                <div class="field">
                    <label for="code">Recovery code</label>
                    <input type="text" id="code" name="code" required placeholder="XXXXX-XXXXX-XXXXX-XXXXX" spellcheck="false">
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="confirm">Repeat it</label>
                    <input type="password" id="confirm" name="confirm" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password">
                </div>
                <button type="submit"><i class="fa-solid fa-unlock-keyhole"></i> Reset password</button>
            </form>
            <p class="login-alt">Have access to the recovery email? <a href="forgot-password.php">Email me a link</a></p>
        <?php endif; ?>
<?php elite_auth_page_close(); ?>
