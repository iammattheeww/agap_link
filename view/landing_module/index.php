<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// CHECK IF USER IS LOGGED IN
$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

// CAPTURE SUCCESS/ERROR MESSAGES (For logout messages)
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/landing_page/style.css">
</head>

<body>
    <?php require VIEW_PATH . '/partials/header.php'; ?>

    <section class="hero" id="home">
        <div class="hero-background"></div>
        <div class="container">

            <?php if ($success): ?>
                <div class="alert alert-success" style="position: relative; z-index: 10; margin-bottom: 20px; text-align: center; background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="position: relative; z-index: 10; margin-bottom: 20px; text-align: center; background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; border-left: 4px solid #ef4444;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="hero-content">
                <span class="badge">
                    <span class="badge-dot"></span>
                    Live City Updates
                </span>
                <h1 class="hero-title">
                    Welcome to<br>
                    <span class="hero-title-highlight">AGAP-Link</span>
                </h1>
                <p class="hero-description">
                    Your ultimate city companion! Report issues, track progress, and stay updated with real-time community announcements.
                </p>
                <div class="hero-actions">
                    <a href="#services" class="btn btn-secondary btn-lg">Explore Services</a>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <?php if ($is_user_logged_in): ?>
                    <h2 class="cta-title">Start Making a Difference Today</h2>
                    <p class="cta-description">
                        Report issues, track progress, and contribute to a better city environment.
                    </p>
                    <a href="<?= BASE_URL ?>/view/user_module/user_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <?php else: ?>
                    <h2 class="cta-title">Ready to make a difference?</h2>
                    <p class="cta-description">
                        Join thousands of citizens contributing to a better city environment today.
                    </p>
                    <a href="<?= BASE_URL ?>/view/auth/index.php" class="btn btn-primary btn-lg">Create Account Now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="<?= ASSET_URL ?>/js/landing/main.js"></script>

    <?php require VIEW_PATH . '/partials/footer.php'; ?>
</body>

</html>