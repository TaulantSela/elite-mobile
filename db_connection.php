<?php
$host="localhost";
$user="root";
$password="";
$dbname="phpproject";

$conn=mysqli_connect($host,$user,$password,$dbname);
if(!$conn){
	echo "Could not connect with mysql!";
	exit;
}

?>