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
    if (!elite_verify_login($conn, $username, $current)) {
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

<?php if ($errors): ?>
    <div class="a-alert error" style="max-width:640px;"><i class="fa-solid fa-circle-exclamation"></i>
        <ul><?php foreach ($errors as $err): ?><li><?php echo $e($err); ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<?php if ($success !== ''): ?>
    <div class="a-alert success" style="max-width:640px;"><i class="fa-solid fa-circle-check"></i> <?php echo $e($success); ?></div>
<?php endif; ?>
<?php if ($newCode !== null): ?>
    <div style="max-width:640px;"><?php elite_render_recovery_code($newCode); ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width:640px;">
    <div class="admin-card-head">
        <div>
            <h2>Change password</h2>
            <p>At least <?php echo ELITE_MIN_PASSWORD_LENGTH; ?> characters. Open reset links stop working.</p>
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

<div class="admin-card" style="max-width:640px;">
    <div class="admin-card-head">
        <div>
            <h2>Recovery email</h2>
            <p>Where "Forgot password?" sends reset links. <?php echo $user['email'] !== null ? 'Currently ' . $e($user['email']) . '.' : 'None set: emailed resets are off.'; ?></p>
        </div>
    </div>
    <div class="admin-card-body">
        <form class="admin-form" action="admin_account.php" method="post" autocomplete="off">
            <input type="hidden" name="action" value="email">
            <div class="field">
                <label for="em-email">Email</label>
                <input type="email" id="em-email" name="email" value="<?php echo $e((string) $user['email']); ?>" placeholder="you@example.com">
            </div>
            <div class="field">
                <label for="em-current">Current password</label>
                <input type="password" id="em-current" name="current" required autocomplete="current-password">
            </div>
            <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-envelope"></i> Save email</button>
        </form>
    </div>
</div>

<div class="admin-card" style="max-width:640px;">
    <div class="admin-card-head">
        <div>
            <h2>Recovery code</h2>
            <p><?php echo $user['has_recovery_code']
                ? 'A recovery code is set. Generating a new one replaces it.'
                : 'No recovery code yet. Create one so you can get in without email.'; ?></p>
        </div>
    </div>
    <div class="admin-card-body">
        <form class="admin-form" action="admin_account.php" method="post" autocomplete="off">
            <input type="hidden" name="action" value="code">
            <div class="field">
                <label for="rc-current">Current password</label>
                <input type="password" id="rc-current" name="current" required autocomplete="current-password">
            </div>
            <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-shield-halved"></i>
                <?php echo $user['has_recovery_code'] ? 'Generate a new recovery code' : 'Create a recovery code'; ?></button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
