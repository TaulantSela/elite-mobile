<?php include("includes/db_connection.php"); include ("includes/functions.php");?>
<!DOCTYPE html>
<html lang="en">
<?php include("includes/head.html") ?>
<body>
<?php include("includes/header.html") ?>

<div class="container-fluid text-center">
    <?php include("includes/leftnav.html") ?>
      <div class="col-sm-3 text-left">
        <?php showProduct(); ?>
      </div>
      <div class="col-sm-5 text-left">
        <?php productDetails() ?>
      </div>
    <?php include("includes/rightnav.html") ?>
  </div>
</div>
<?php include("includes/footer.html") ?>
</body>
</html>
