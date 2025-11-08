<?php
declare(strict_types=1);

session_start();

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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Form - EliteMobile</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="styles/login_style.css" media="all" />
</head>
<body>
<a href="home.php"><img src="img/elite-mobile_logo.png" class="img-rounded" width="200" alt="Elite Mobile"/></a>
<div class="login">
    <h1>Admin Login</h1>
    <form method="POST" action="LoginForm.php" autocomplete="off">
        <input type="text" name="username" placeholder="Email" required="required" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        <input type="password" name="password" placeholder="Password" required="required" />
        <button type="submit" class="btn btn-primary btn-block btn-large" name="login">Login</button>
        <?php if ($error !== ''): ?>
            <p class="text-danger" style="margin-top: 15px;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
