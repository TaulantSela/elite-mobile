<?php
declare(strict_types=1);

/** Shared shell for the signed-out pages (forgot / reset / recover), styled like the sign-in card. */

require_once __DIR__ . '/url_helpers.php';

function elite_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function elite_auth_page_open(string $title, string $heading, string $subtitle): void
{
    $h = 'elite_h';
    header('Referrer-Policy: no-referrer'); // keep reset tokens out of Referer headers
    header('X-Frame-Options: DENY');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?php echo $h($title); ?> · Elite Mobile Admin</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $h(elite_asset('favicon.ico')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?php echo $h(elite_asset('styles/admin.css')); ?>">
</head>
<body class="admin-login-body">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo $h(elite_asset('img/elite-mobile_logo.png')); ?>" alt="Elite Mobile">
            <strong>Elite Mobile</strong>
        </div>
        <h1><?php echo $h($heading); ?></h1>
        <p class="sub"><?php echo $h($subtitle); ?></p>
    <?php
}

function elite_auth_page_close(): void
{
    ?>
        <a class="back-home" href="LoginForm.php"><i class="fa-solid fa-arrow-left"></i> Back to sign in</a>
    </div>
</body>
</html>
    <?php
}

/** A one-time recovery code, shown with a warning to save it. */
function elite_render_recovery_code(string $code): void
{
    $h = 'elite_h';
    ?>
    <div class="recovery-code-box">
        <span>Your recovery code</span>
        <code><?php echo $h($code); ?></code>
        <small>Save it in your password manager now. It is shown only once, and using it replaces it with a new one.</small>
    </div>
    <?php
}
