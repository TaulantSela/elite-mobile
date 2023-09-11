<?php
include('./db_connection.php');

if(isset($_GET['amt']) and isset($_GET['item_number']) and isset($_GET['st']) && $_GET['st'] == 'Completed'){
$orderid=$_GET['item_number'];
$query = "UPDATE `product` SET `paid` = '1' WHERE `product`.`productid` = '$orderid';";
$result = mysqli_query($conn , $query);

        if($result){
            header('Location: http://localhost/sela/description.php?productid='.$orderid.'&pay=success');
            exit;
        }
}else{
     header('Location: http://localhost/sela/description.php?productid='.$orderid.'&pay=error');
            exit;
}
 
?>