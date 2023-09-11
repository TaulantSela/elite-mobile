<?php 
session_start();

?>
<!DOCTYPE>
<html>
	<head>
		<title>Login Form - EliteMobile</title>
<link rel="stylesheet" href="styles/login_style.css" media="all" /> 


	</head>
<body>
<div class="login">	
	<h1>Admin Login</h1>
    <form method="POST" action="LoginForm.php">
    	<input type="text" name="username" placeholder="Email" required="required" />
        <input type="password" name="password" placeholder="Password" required="required" />
        <button type="submit" class="btn btn-primary btn-block btn-large" name="login">Login</button>
    </form>
</div>


</body>
</html>
<?php 

include ("db_connection.php"); 
	
	if(isset($_POST['login']))
	{
	
	$username=$_POST["username"];
	$password=$_POST["password"];
	$cp=md5($password);
	
	$query="SELECT * FROM users WHERE username ='$username' AND password='$password'";
	$result=mysqli_query($conn,$query);
	$nr=mysqli_num_rows($result);
	if($nr==1)
	{
        $_SESSION["username"]=$username; 
//        header("Location:admin_products.php");
        echo "<script>window.open('admin_products.php','_self')</script>";	
	}
	
	else{
		echo "<script>alert('Try again!')</script>";
		echo "<script>window.open('_self')</script>";
	}
	}
?>