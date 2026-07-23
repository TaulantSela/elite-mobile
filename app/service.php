<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$services = fetchAllServices();
$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

$iconMap = [
    'display' => 'fa-display',
    'screen'  => 'fa-display',
    'battery' => 'fa-battery-full',
    'water'   => 'fa-droplet',
    'board'   => 'fa-microchip',
    'logic'   => 'fa-microchip',
    'data'    => 'fa-database',
    'consult' => 'fa-briefcase',
    'enterprise' => 'fa-briefcase',
];
$iconFor = static function (string $icon) use ($iconMap): string {
    $key = strtolower($icon);
    return $iconMap[$key] ?? 'fa-screwdriver-wrench';
};
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="home.php">Home</a>
            <span class="sep">/</span>
            <span>Services</span>
        </nav>
        <h1>Repair lab &amp; services</h1>
        <p>Certified technicians, OEM-grade parts, and transparent pricing. Book online and track your device in real time.</p>
    </div>
</section>

<main class="services-page">
    <div class="container">
        <?php if ($services === []): ?>
            <div class="empty-state">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <h3>No services listed yet</h3>
                <p>Please check back soon or contact us directly.</p>
            </div>
        <?php else: ?>
            <div class="service-grid">
                <?php foreach ($services as $service): ?>
                    <article class="service-card">
                        <span style="display:inline-flex;width:48px;height:48px;border-radius:12px;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(37,99,235,0.9),rgba(168,85,247,0.9));color:#fff;font-size:1.35rem;margin-bottom:1rem;box-shadow:0 8px 20px rgba(37,99,235,0.35);">
                            <i class="fa-solid <?php echo $e($iconFor((string) $service['icon'])); ?>"></i>
                        </span>
                        <?php if ($service['is_featured']): ?>
                            <span class="service-pill" style="position:absolute;top:1.25rem;right:1.25rem;"><i class="fa-solid fa-star"></i> Popular</span>
                        <?php endif; ?>
                        <h3><?php echo $e($service['name']); ?></h3>
                        <p class="svc-desc"><?php echo $e($service['short_description']); ?></p>
                        <div class="service-meta">
                            <span><?php echo formatPrice($service['price']); ?></span>
                            <?php if (!empty($service['duration'])): ?>
                                <span class="service-pill"><i class="fa-regular fa-clock"></i> <?php echo $e((string) $service['duration']); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="services-cta">
            <div>
                <h2>Ready to fix your device?</h2>
                <p>Walk-in or book ahead — most repairs are done the same day.</p>
            </div>
            <div class="cta-actions">
                <a class="btn-light-outline" style="background:#fff;" href="contact.php"><i class="fa-solid fa-calendar-check"></i> Book a repair</a>
                <a class="btn-dark" href="contact.php"><i class="fa-solid fa-comments"></i> Talk to a specialist</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
