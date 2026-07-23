<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$product = findProductFromRequest();
$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>

<?php if ($product === null): ?>
    <main class="shop">
        <div class="container">
            <div class="empty-state">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Product not found</h3>
                <p>The device you’re looking for isn’t available.</p>
                <p style="margin-top:1.25rem;"><a class="btn-dark" href="products.php"><i class="fa-solid fa-arrow-left"></i> Back to all phones</a></p>
            </div>
        </div>
    </main>
<?php else:
    $name = $e($product['productname']);
    $brand = $e($product['categoryname']);
    $isPaid = (int) $product['paid'] === 1;
?>
    <main class="pdp">
        <div class="container">
            <a class="back-link" href="products.php?categoryid=<?php echo (int) $product['categoryid']; ?>">
                <i class="fa-solid fa-arrow-left"></i> Back to <?php echo $brand; ?>
            </a>
            <div class="pdp-grid">
                <div class="pdp-media">
                    <img src="img/<?php echo $e($product['image']); ?>" alt="<?php echo $name; ?>">
                </div>
                <div class="pdp-body">
                    <span class="pdp-brand"><i class="fa-solid fa-mobile-screen"></i> <?php echo $brand; ?></span>
                    <h1 class="pdp-title"><?php echo $name; ?></h1>
                    <div>
                        <span class="pdp-price"><?php echo formatPrice((int) $product['price']); ?></span>
                        <?php if ($isPaid): ?>
                            <span class="stock-pill is-paid"><i class="fa-solid fa-circle-check"></i> Reserved</span>
                        <?php else: ?>
                            <span class="stock-pill"><i class="fa-solid fa-circle-check"></i> In stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="pdp-specs">
                        <h3><i class="fa-solid fa-list-check"></i> Specifications</h3>
                        <div class="spec-body"><?php echo renderProductInfo((string) $product['info']); ?></div>
                    </div>

                    <div class="pdp-cta">
                        <a class="btn-dark" href="contact.php"><i class="fa-solid fa-cart-plus"></i> Preorder now</a>
                        <a class="btn-light-outline" href="service.php"><i class="fa-solid fa-screwdriver-wrench"></i> Book a repair</a>
                    </div>

                    <?php if (!$isPaid): ?>
                        <div style="margin-top:1rem; display:flex; align-items:center; gap:0.6rem; color:var(--em-ink-soft); font-size:0.9rem;">
                            <span>Secure checkout demo:</span>
                            <?php renderPaypalForm((int) $product['productid'], $product['productname'], (float) $product['price']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="pdp-trust">
                        <div><i class="fa-solid fa-truck-fast"></i> 48h nationwide delivery</div>
                        <div><i class="fa-solid fa-shield-halved"></i> 24-month warranty</div>
                        <div><i class="fa-solid fa-rotate-left"></i> 14-day returns</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
