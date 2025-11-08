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
    <?php require_once __DIR__ . '/includes/leftnav.html'; ?>
      <div class="col-sm-3 text-left">
        <?php showProduct(); ?>
      </div>
      <div class="col-sm-5 text-left">
        <?php productDetails() ?>
      </div>
    <?php require_once __DIR__ . '/includes/rightnav.html'; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
