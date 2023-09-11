<?php
session_start();
session_destroy();
echo "<script>window.open('LoginForm.php','_self')</script>";
?> 