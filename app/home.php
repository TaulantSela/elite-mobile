<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$categories = fetchAllCategories();
$featured = fetchFeaturedProducts(6);
$newArrivals = fetchLatestProducts(6);
$services = fetchHighlightedServices(4);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>
<main>
    <section class="hero">
        <div class="container">
            <div class="hero-copy">
                <span class="hero-badge"><i class="fa-solid fa-bolt"></i> Flagship Drop</span>
                <h1>Level up to the future of mobile tech.</h1>
                <p>Discover curated devices, preorder exclusives, and premium services trusted by enthusiasts, creators, and pros across Macedonia.</p>
                <div class="hero-actions">
                    <a class="btn-primary" href="products.php">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        Browse all phones
                    </a>
                    <a class="btn-outline" href="service.php">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        Book a repair
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>120+</strong>
                        Launch-day flagship models ready to ship.
                    </div>
                    <div class="hero-stat">
                        <strong>48h</strong>
                        Express delivery nationwide for in-stock units.
                    </div>
                    <div class="hero-stat">
                        <strong>5★</strong>
                        Certified repair lab with OEM-grade parts.
                    </div>
                </div>
            </div>
            <div class="hero-artwork">
                <div class="hero-device-card">
                    <img src="img/phoneheader.jpg" alt="Featured flagship smartphone stack">
                    <span class="badge-deal"><i class="fa-solid fa-fire"></i> Week one preorder bonuses live</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading">
                <h2>Shop by ecosystem</h2>
                <p>Select your favourite brand and explore devices, accessories, and bundles tailored to it.</p>
            </div>
            <div class="category-strip">
                <?php foreach ($categories as $category): ?>
                    <a class="category-card" href="products.php?categoryid=<?php echo $category['id']; ?>">
                        <span><i class="fa-solid fa-mobile-screen"></i></span>
                        <h3><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p>Discover the latest releases and signature accessories curated for <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?> devices.</p>
                    </a>
                <?php endforeach; ?>
                <a class="category-card" href="service.php">
                    <span><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <h3>Repair studio</h3>
                    <p>Glass, batteries, logic boards, and deep diagnostics performed by certified technicians.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading">
                <h2>Fresh arrivals</h2>
                <p>Be among the first to hold the latest flagship experiences, packed with cutting-edge silicon and pro-level cameras.</p>
            </div>
            <div class="product-grid">
                <?php foreach ($newArrivals as $product): ?>
                    <article class="product-card">
                        <div class="product-photo">
                            <img src="img/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="product-body">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                            <div class="product-actions">
                                <a href="description.php?productid=<?php echo $product['id']; ?>">View details</a>
                                <a href="contact.php" class="text-muted"><i class="fa-solid fa-cart-plus"></i> Preorder</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" style="background: #fff;">
        <div class="container">
            <div class="section-heading">
                <h2>Pro picks</h2>
                <p>Hand-picked devices for creators, business travel, and mobile gamers who need maximum performance.</p>
            </div>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="product-card">
                        <div class="product-photo">
                            <img src="img/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="product-body">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                            <div class="product-actions">
                                <a href="description.php?productid=<?php echo $product['id']; ?>">View details</a>
                                <a href="contact.php" class="text-muted"><i class="fa-solid fa-headset"></i> Consult</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" style="background: #0f172a;">
        <div class="container">
            <div class="section-heading" style="color: #f8fafc;">
                <h2 style="color: #f8fafc;">Repair lab &amp; services</h2>
                <p style="color: rgba(226, 232, 240, 0.75);">Certified technicians, premium parts, transparent pricing. Book online and track your device in real time.</p>
            </div>
            <div class="service-grid">
                <?php foreach ($services as $service): ?>
                    <article class="service-card">
                        <h3><?php echo htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($service['short_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="service-meta">
                            <span><?php echo formatPrice($service['price']); ?></span>
                            <?php if (!empty($service['duration'])): ?>
                                <span class="service-pill"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($service['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="cta-actions" style="margin-top: 2.5rem; justify-content: center;">
                <a class="btn-primary" href="service.php"><i class="fa-solid fa-calendar-check"></i> Schedule a repair</a>
                <a class="btn-outline" href="contact.php"><i class="fa-solid fa-comments"></i> Talk to a specialist</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-banner">
                <div>
                    <h2>Need enterprise or bulk fulfilment?</h2>
                    <p>We power corporate rollouts, hospitality upgrades, and developer labs with tailored pricing and lifecycle management.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn-primary" href="contact.php"><i class="fa-solid fa-briefcase"></i> Book a strategy call</a>
                    <a class="btn-outline" href="about.php"><i class="fa-solid fa-circle-info"></i> Learn more</a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
