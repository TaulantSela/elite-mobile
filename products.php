<?php include("includes/db_connection.php"); include ("includes/functions.php");
?>
<!DOCTYPE html>
<html lang="en">
<?php include("includes/head.html") ?>
<body>
<?php include("includes/header.html") ?>
<div class="container-fluid text-center">
    <div class="col-sm-2 sidenav" style="">
        <?php showCategory(); ?>
    </div>

    <!-- content -->
    <div class="col-sm-8 text-left">
        <?php
        if(!isset($_GET["categoryid"]))
        {
            echo "<br>";
            echo "<h1 align='center'>Please Select a Category !!</h1>";
        }
        else
        {
            selectOrder();
            order();
        }
        ?>
    </div>
    <?php include ("includes/rightnav.html")?>
</div>
<?php include("includes/footer.html") ?>
</body>
<?php include("includes/order.html") ?>
</html>