<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/url_helpers.php';

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please provide both username and password.';
    } else {
        $statement = mysqli_prepare($conn, 'SELECT username FROM users WHERE username = ? AND password = ? LIMIT 1');

        if ($statement && mysqli_stmt_bind_param($statement, 'ss', $username, $password) && mysqli_stmt_execute($statement)) {
            $result = mysqli_stmt_get_result($statement);

            if ($result && mysqli_num_rows($result) === 1) {
                $_SESSION['username'] = $username;
                mysqli_stmt_close($statement);
                header('Location: dashboard.php');
                exit;
            }

            if ($result) {
                mysqli_free_result($result);
            }
        }

        if ($statement) {
            mysqli_stmt_close($statement);
        }

        $error = 'Invalid credentials. Please try again.';
    }
}

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Elite Mobile Admin</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $e(elite_asset('favicon.ico')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?php echo $e(elite_asset('styles/admin.css')); ?>">
</head>
<body class="admin-login-body">
    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo $e(elite_asset('img/elite-mobile_logo.png')); ?>" alt="Elite Mobile">
            <strong>Elite Mobile</strong>
        </div>
        <h1>Welcome back</h1>
        <p class="sub">Sign in to manage products, categories, and services.</p>

        <?php if ($error !== ''): ?>
            <div class="login-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $e($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="LoginForm.php" autocomplete="off">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?php echo $e($_POST['username'] ?? ''); ?>" placeholder="admin">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" name="login"><i class="fa-solid fa-right-to-bracket"></i> Sign in</button>
        </form>
        <a class="back-home" href="home.php"><i class="fa-solid fa-arrow-left"></i> Back to store</a>
    </div>
</body>
</html>
