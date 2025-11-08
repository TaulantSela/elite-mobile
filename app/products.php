<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>
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
            renderProducts();
        }
        ?>
    </div>
    <?php require_once __DIR__ . '/includes/rightnav.html'; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
<?php require_once __DIR__ . '/includes/order.html'; ?>
</html>