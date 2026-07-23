<?php
declare(strict_types=1);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['userName'] ?? '');
    $fromEmail = filter_var(trim($_POST['from_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['content'] ?? '');

    if ($name === '' || $fromEmail === false || $subject === '' || $body === '') {
        $message = 'Please complete all required fields with valid information.';
        $messageType = 'error';
    } else {
        $toEmail = 'taulant1995@gmail.com';
        $headers = sprintf('From: %s <%s>', $name, $fromEmail);

        if (mail($toEmail, $subject, $body, $headers)) {
            $message = 'Thanks! Your message has been sent — we’ll get back to you shortly.';
            $messageType = 'success';
        } else {
            $message = 'Email sending failed. Please try again later, or call us directly.';
            $messageType = 'error';
        }
    }
}

$formAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
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
            <span>Support</span>
        </nav>
        <h1>Get in touch</h1>
        <p>Questions about a device, an order, or a repair? Send us a message or reach us directly — we usually reply within a few hours.</p>
    </div>
</section>

<main class="contact-layout">
    <div class="container">
      <div class="contact-grid">
        <div class="contact-card">
            <h2>Send us a message</h2>
            <p class="muted">Fill in the form and our team will get back to you by email.</p>

            <?php if ($message !== ''): ?>
                <div class="form-alert <?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                    <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <?php echo $e($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo $formAction; ?>">
                <div class="form-field">
                    <label for="userName">Name</label>
                    <input type="text" id="userName" name="userName" required value="<?php echo $e($_POST['userName'] ?? ''); ?>" placeholder="Your full name">
                </div>
                <div class="form-field">
                    <label for="from_email">Email</label>
                    <input type="email" id="from_email" name="from_email" required value="<?php echo $e($_POST['from_email'] ?? ''); ?>" placeholder="you@example.com">
                </div>
                <div class="form-field">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required value="<?php echo $e($_POST['subject'] ?? ''); ?>" placeholder="How can we help?">
                </div>
                <div class="form-field">
                    <label for="content">Message</label>
                    <textarea id="content" name="content" required placeholder="Tell us a bit more…"><?php echo $e($_POST['content'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="submit" class="btn-dark"><i class="fa-solid fa-paper-plane"></i> Send message</button>
            </form>
        </div>

        <div>
            <div class="info-card">
                <h2>Reach us directly</h2>
                <p class="muted">Prefer to talk? Here’s where to find us.</p>
                <div class="info-item">
                    <span class="info-ic"><i class="fa-solid fa-phone"></i></span>
                    <div><div class="info-label">Call us</div><div class="info-value"><a href="tel:+38970477300">+389 70 477 300</a></div></div>
                </div>
                <div class="info-item">
                    <span class="info-ic"><i class="fa-solid fa-envelope"></i></span>
                    <div><div class="info-label">Email</div><div class="info-value"><a href="mailto:hello@elitemobile.mk">hello@elitemobile.mk</a></div></div>
                </div>
                <div class="info-item">
                    <span class="info-ic"><i class="fa-solid fa-location-dot"></i></span>
                    <div><div class="info-label">Visit</div><div class="info-value">Skopje City Mall, Level 1</div></div>
                </div>
                <div class="info-item">
                    <span class="info-ic"><i class="fa-regular fa-clock"></i></span>
                    <div><div class="info-label">Hours</div><div class="info-value">Mon–Sat, 10:00 – 21:00</div></div>
                </div>
            </div>
            <div class="info-card">
                <h2>Need a repair?</h2>
                <p class="muted">Book a certified repair or ask about turnaround times.</p>
                <a class="btn-light-outline" href="service.php"><i class="fa-solid fa-screwdriver-wrench"></i> Explore services</a>
            </div>
        </div>
      </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
