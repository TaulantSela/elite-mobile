<?php
session_start();

include("../includes/db_connection.php");
require_once __DIR__ . "/../includes/auth.php";

$login_error = "";

if (isset($_POST['login'])) {
	$username = $_POST["username"] ?? "";
	$password = $_POST["password"] ?? "";

	if (verify_admin_login($conn, $username, $password)) {
		$_SESSION["username"] = $username;
		header("Location: admin_products.php");
		exit;
	}

	$login_error = "Invalid username or password.";
}
?>
<!DOCTYPE>
<html>
	<head>
		<title>Login Form - EliteMobile</title>
<link rel="stylesheet" href="../styles/login_style.css" media="all" />


	</head>
<body>
<a href="../home.php"><img src="../img/logo.png" class="img-rounded" width="200px"/></a>
<div class="login">
	<h1>Admin Login</h1>
    <form method="POST" action="LoginForm.php">
    	<input type="text" name="username" placeholder="Email" required="required" />
        <input type="password" name="password" placeholder="Password" required="required" />
        <button type="submit" class="btn btn-primary btn-block btn-large" name="login">Login</button>
        <?php if ($login_error !== ""): ?>
            <p style="color:#c0392b; margin-top:12px;"><?php echo htmlspecialchars($login_error, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>
    </form>
</div>


</body>
</html>
