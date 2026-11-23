<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/../includes/account.php';
require_once __DIR__ . '/../includes/auth_page.php';

/** @var mysqli $conn */
$username = (string) $_SESSION['username'];
$errors = [];
$success = '';
$newCode = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $current = (string) ($_POST['current'] ?? '');

    // Every change here needs the current password, so a hijacked session alone can't take over the account.
    if (($lockMinutes = elite_login_lock_minutes($conn, $username)) > 0) {
        $errors[] = elite_lock_message($lockMinutes);
    } elseif (!elite_verify_login($conn, $username, $current)) {
        elite_record_login_failure($conn, $username);
        $errors[] = 'Your current password is not correct.';
    } elseif ($action === 'password') {
        $password = (string) ($_POST['password'] ?? '');
        $problem = elite_password_problem($password, (string) ($_POST['confirm'] ?? ''));

        if ($problem !== null) {
            $errors[] = $problem;
        } else {
            elite_set_password($conn, $username, $password);
            elite_cancel_reset_links($conn, $username);
            session_regenerate_id(true);
            $success = 'Your password has been changed.';
        }
    } elseif ($action === 'email') {
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Enter a valid email address, or leave it empty to remove it.';
        } else {
            elite_set_email($conn, $username, $email === '' ? null : $email);
            $success = $email === '' ? 'Recovery email removed.' : 'Recovery email saved.';
        }
    } elseif ($action === 'code') {
        $newCode = elite_new_recovery_code($conn, $username);
        $success = 'New recovery code created. The old one no longer works.';
    }
}

$user = elite_find_user($conn, $username) ?? ['email' => null, 'has_recovery_code' => false];
$e = 'elite_h';
$adminTitle = 'Account';
$adminActive = 'account';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="account-page">
    <?php if ($errors): ?>
        <div class="a-alert error"><i class="fa-solid fa-circle-exclamation"></i>
            <ul><?php foreach ($errors as $err): ?><li><?php echo $e($err); ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <div class="a-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo $e($success); ?></div>
    <?php endif; ?>
    <?php if ($newCode !== null): ?>
        <?php elite_render_recovery_code($newCode); ?>
    <?php endif; ?>

    <div class="admin-grid account-grid">
        <div class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2><i class="fa-solid fa-key"></i> Change password</h2>
                    <p>At least <?php echo ELITE_MIN_PASSWORD_LENGTH; ?> characters. Any open reset links stop working.</p>
                </div>
            </div>
            <div class="admin-card-body">
                <form class="admin-form" action="admin_account.php" method="post" autocomplete="off">
                    <input type="hidden" name="action" value="password">
                    <div class="field">
                        <label for="pw-current">Current password</label>
                        <input type="password" id="pw-current" name="current" required autocomplete="current-password">
                    </div>
                    <div class="field">
                        <label for="pw-new">New password</label>
                        <input type="password" id="pw-new" name="password" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label for="pw-confirm">Repeat new password</label>
                        <input type="password" id="pw-confirm" name="confirm" required minlength="<?php echo ELITE_MIN_PASSWORD_LENGTH; ?>" autocomplete="new-password">
                    </div>
                    <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-key"></i> Change password</button>
                </form>
            </div>
        </div>

        <div class="admin-grid">
            <div class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2><i class="fa-solid fa-envelope"></i> Recovery email</h2>
                        <p>Where "Forgot password?" sends reset links.</p>
                    </div>
                    <?php if ($user['email'] !== null): ?>
                        <span class="status-pill ok"><i class="fa-solid fa-circle-check"></i> On</span>
                    <?php else: ?>
                        <span class="status-pill off"><i class="fa-solid fa-circle-minus"></i> Off</span>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form class="admin-form" action="admin_account.php" method="post" autocomplete="off">
                        <input type="hidden" name="action" value="email">
                        <div class="field">
                            <label for="em-email">Email</label>
                            <input type="email" id="em-email" name="email" value="<?php echo $e((string) $user['email']); ?>" placeholder="you@example.com">
                            <div class="hint">Leave empty to turn emailed resets off.</div>
                        </div>
                        <div class="confirm-row">
                            <div class="field">
                                <label for="em-current">Current password</label>
                                <input type="password" id="em-current" name="current" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2><i class="fa-solid fa-shield-halved"></i> Recovery code</h2>
                        <p>Resets your password without email. Shown once when created.</p>
                    </div>
                    <?php if ($user['has_recovery_code']): ?>
                        <span class="status-pill ok"><i class="fa-solid fa-circle-check"></i> Set</span>
                    <?php else: ?>
                        <span class="status-pill off"><i class="fa-solid fa-circle-minus"></i> Not set</span>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form class="admin-form" action="admin_account.php" method="post" autocomplete="off">
                        <input type="hidden" name="action" value="code">
                        <div class="confirm-row">
                            <div class="field">
                                <label for="rc-current">Current password</label>
                                <input type="password" id="rc-current" name="current" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-rotate"></i>
                                <?php echo $user['has_recovery_code'] ? 'Replace code' : 'Create code'; ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
