<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/../includes/account.php';
require_once __DIR__ . '/../includes/auth_page.php';

/**
 * Admin accounts: list, invite, change email, remove.
 *
 * Changes need the acting admin's password, confirmed once and then unlocked
 * for 10 minutes, so a hijacked session alone can't add a backdoor account or
 * lock the owner out. Every action redirects back here (no resubmits on reload).
 */

/** @var mysqli $conn */
const ELITE_UNLOCK_SECONDS = 600;

$me = (string) $_SESSION['username'];

function elite_users_done(string $type, string $message): never
{
    $_SESSION['users_flash'] = [$type, $message];
    header('Location: admin_users.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $target = trim((string) ($_POST['username'] ?? ''));
    $email = elite_normalize_email((string) ($_POST['email'] ?? ''));

    if ($action === 'unlock') {
        if (($lockMinutes = elite_login_lock_minutes($conn, $me)) > 0) {
            elite_users_done('error', elite_lock_message($lockMinutes));
        }

        if (!elite_verify_login($conn, $me, (string) ($_POST['current'] ?? ''))) {
            elite_record_login_failure($conn, $me);
            elite_users_done('error', 'Your password is not correct.');
        }

        $_SESSION['users_unlocked_until'] = time() + ELITE_UNLOCK_SECONDS;
        elite_users_done('success', 'Changes are unlocked for 10 minutes.');
    }

    if ((int) ($_SESSION['users_unlocked_until'] ?? 0) <= time()) {
        elite_users_done('error', 'Confirm your password first.');
    }

    if ($action === 'add') {
        $problem = elite_create_admin($conn, $target, $email, $me);
        $problem === null
            ? elite_users_done('success', "Invite sent to {$email}. {$target} can sign in after choosing a password.")
            : elite_users_done('error', $problem);
    }

    if ($action === 'resend') {
        elite_send_invite($conn, $target, $me)
            ? elite_users_done('success', "A new invite is on its way to {$target}.")
            : elite_users_done('error', 'No invite sent: the account is already set up, or three links were sent in the last hour.');
    }

    if ($action === 'email') {
        $problem = elite_request_email_change($conn, $target, $email);
        $problem === null
            ? elite_users_done('success', "Confirmation link sent to {$email}. The email of {$target} changes when it is clicked.")
            : elite_users_done('error', $problem);
    }

    if ($action === 'cancel_email') {
        elite_cancel_email_changes($conn, $target);
        elite_users_done('success', "Pending email change for {$target} cancelled.");
    }

    if ($action === 'remove') {
        if (strcasecmp($target, $me) === 0) {
            elite_users_done('error', "You can't remove your own account.");
        }

        if (elite_find_user($conn, $target) === null) {
            elite_users_done('error', 'That account no longer exists.');
        }

        elite_delete_admin($conn, $target);
        elite_users_done('success', "{$target} has been removed and signed out.");
    }

    elite_users_done('error', 'Unknown action.');
}

$flash = $_SESSION['users_flash'] ?? null;
unset($_SESSION['users_flash']);
$unlockedFor = (int) ($_SESSION['users_unlocked_until'] ?? 0) - time();
$unlocked = $unlockedFor > 0;
$admins = elite_list_admins($conn);
$e = 'elite_h';
$adminTitle = 'Users';
$adminActive = 'users';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

<div class="account-page">
    <?php if ($flash !== null): ?>
        <div class="a-alert <?php echo $flash[0] === 'success' ? 'success' : 'error'; ?>">
            <i class="fa-solid <?php echo $flash[0] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> <?php echo $e($flash[1]); ?>
        </div>
    <?php endif; ?>

    <?php if (!$unlocked): ?>
        <div class="admin-card unlock-card">
            <div class="admin-card-head">
                <div>
                    <h2><i class="fa-solid fa-lock"></i> Confirm it's you</h2>
                    <p>Adding or removing admins and changing their emails needs your password. Changes then stay unlocked for 10 minutes.</p>
                </div>
            </div>
            <div class="admin-card-body">
                <form class="admin-form" action="admin_users.php" method="post" autocomplete="off">
                    <input type="hidden" name="action" value="unlock">
                    <div class="confirm-row">
                        <div class="field">
                            <label for="un-current">Your password</label>
                            <input type="password" id="un-current" name="current" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-lock-open"></i> Unlock</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <p class="unlock-note"><i class="fa-solid fa-lock-open"></i> Changes unlocked for <?php echo (int) ceil($unlockedFor / 60); ?> more minute<?php echo $unlockedFor > 60 ? 's' : ''; ?>.</p>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2><i class="fa-solid fa-users"></i> Admins</h2>
                <p><?php echo count($admins); ?> account<?php echo count($admins) === 1 ? '' : 's'; ?>. Every admin has full access to the panel.</p>
            </div>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Username</th><th>Email</th><th>Status</th><?php if ($unlocked): ?><th style="text-align:right;">Actions</th><?php endif; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($admins as $admin): ?>
                    <?php $isMe = strcasecmp($admin['username'], $me) === 0; ?>
                    <tr>
                        <td class="t-name"><?php echo $e($admin['username']); ?><?php if ($isMe): ?> <span class="a-tag">You</span><?php endif; ?></td>
                        <td>
                            <?php echo $e($admin['email']); ?>
                            <?php if ($admin['pending_email'] !== null): ?>
                                <div class="pending-line">
                                    <i class="fa-solid fa-hourglass-half"></i> Changing to <?php echo $e($admin['pending_email']); ?>, waiting for confirmation
                                    <?php if ($unlocked): ?>
                                        <form class="inline-form" action="admin_users.php" method="post">
                                            <input type="hidden" name="action" value="cancel_email">
                                            <input type="hidden" name="username" value="<?php echo $e($admin['username']); ?>">
                                            <button type="submit" class="link-btn">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($admin['email_verified']): ?>
                                <span class="status-pill ok"><i class="fa-solid fa-circle-check"></i> Active</span>
                            <?php else: ?>
                                <span class="status-pill off"><i class="fa-solid fa-envelope"></i> Invite pending</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($unlocked): ?>
                            <td>
                                <div class="row-actions" style="justify-content:flex-end;">
                                    <?php if (!$admin['email_verified']): ?>
                                        <form class="inline-form" action="admin_users.php" method="post">
                                            <input type="hidden" name="action" value="resend">
                                            <input type="hidden" name="username" value="<?php echo $e($admin['username']); ?>">
                                            <button type="submit" class="icon-btn" title="Resend invite"><i class="fa-solid fa-paper-plane"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!$isMe): ?>
                                        <form class="inline-form" action="admin_users.php" method="post"
                                            onsubmit="return confirm('Remove <?php echo $e($admin['username']); ?>? They lose access immediately.');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="username" value="<?php echo $e($admin['username']); ?>">
                                            <button type="submit" class="icon-btn danger" title="Remove"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($unlocked): ?>
        <div class="admin-grid account-grid users-forms">
            <div class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2><i class="fa-solid fa-user-plus"></i> Add an admin</h2>
                        <p>They get an email link to choose their own password. It works for 72 hours.</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <form class="admin-form" action="admin_users.php" method="post" autocomplete="off">
                        <input type="hidden" name="action" value="add">
                        <div class="field">
                            <label for="add-username">Username</label>
                            <input type="text" id="add-username" name="username" required pattern="[A-Za-z0-9._\-]{3,50}" placeholder="e.g. ana">
                            <div class="hint">3 to 50 letters, digits, dots, dashes or underscores.</div>
                        </div>
                        <div class="field">
                            <label for="add-email">Email</label>
                            <input type="email" id="add-email" name="email" required placeholder="ana@example.com">
                        </div>
                        <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-paper-plane"></i> Send invite</button>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-head">
                    <div>
                        <h2><i class="fa-solid fa-envelope"></i> Change an admin's email</h2>
                        <p>It changes once the link sent to the new address is clicked. The current address gets a heads-up.</p>
                    </div>
                </div>
                <div class="admin-card-body">
                    <form class="admin-form" action="admin_users.php" method="post" autocomplete="off">
                        <input type="hidden" name="action" value="email">
                        <div class="field">
                            <label for="ch-username">Admin</label>
                            <select id="ch-username" name="username" required>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?php echo $e($admin['username']); ?>"><?php echo $e($admin['username'] . ' (' . $admin['email'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="ch-email">New email</label>
                            <input type="email" id="ch-email" name="email" required placeholder="new@example.com">
                        </div>
                        <button type="submit" class="abtn abtn-grad"><i class="fa-solid fa-paper-plane"></i> Send confirmation link</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
