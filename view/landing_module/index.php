<?php
require_once __DIR__ . '/config/init.php';

// CHECK IF USER IS LOGGED IN
$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/favicon_io/favicon.ico">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landing_page/style.css">
</head>

<body>
    <?php require VIEW_PATH . 'partials/header.php'; ?>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-background"></div>
        <div class="container">
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
                    Your ultimate city companion! Access services, report issues, and stay connected with your community in real-time.
                </p>

                <!-- CONDITIONAL HERO BUTTONS -->
                <div class="hero-actions">
                    <?php if ($is_user_logged_in): ?>
                        <!-- IF USER IS LOGGED IN, IT SHOWS THE DASHBOARD AND DOWNLOAD APP BUTTONS -->
                        <a href="<? BASE_URL ?>/view/user_module/user_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        <a href="#services" class="btn btn-secondary btn-lg">Learn More</a>
                    <?php else: ?>
                        <!-- IF USER IS NOT LOGGED IN, IT SHOWS THE DOWNLOAD APP AND LEARN MORE CTA BUTTONS -->
                        <a href="<? BASE_URL ?>/view/auth/index.php" class="btn btn-primary btn-lg">Get Started</a>
                        <a href="#services" class="btn btn-secondary btn-lg">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon warning">
                        <img src="<?= BASE_URL ?>/assets/icons/alert_icon.png" alt="Report Hazard">
                    </div>
                    <h3 class="feature-title">Report Hazards</h3>
                    <p class="feature-description">Spot an issue? Report it instantly and help keep our city safe.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon refresh">
                        <img src="<?= BASE_URL ?>/assets/icons/refresh_icon.png" alt="Refresh Updates">
                    </div>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-description">Stay informed with live notifications about your reported issues.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon check">
                        <img src="<?= BASE_URL ?>/assets/icons/check_icon.png" alt="Check Status">
                    </div>
                    <h3 class="feature-title">Track Progress</h3>
                    <p class="feature-description">Monitor the status of community projects and service requests.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about" id="services">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-label">WHO WE ARE</span>
                    <h2 class="section-title">About Agap-Link</h2>
                    <p class="about-text">
                        We bridge the gap between citizens and city administration. Our platform empowers you to be an active participant in your community's growth and safety.
                    </p>
                    <ul class="about-list">
                        <li>
                            <img src="<?= BASE_URL ?>/assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />
                            <span>Direct line to city services</span>
                        </li>
                        <li>
                            <img src="<?= BASE_URL ?>/assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />

                            <span>Transparent tracking of issues</span>
                        </li>
                        <li>
                            <img src="<?= BASE_URL ?>/assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />
                            <span>Community-driven improvements</span>
                        </li>
                    </ul>
                </div>

                <div class="about-image">
                    <div class="image-wrapper">
                        <div class="image-placeholder">
                            <img src="<?= BASE_URL ?>/assets/images/landing_about.jpg" alt="About Agap-Link" class="about-img">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ANNOUNCEMENTS SECTION -->
    <section class="announcements" id="announcements">
        <div class="container">
            <div class="announcements-header">
                <div>
                    <span class="section-label">STAY UPDATED</span>
                    <h2 class="section-title">Latest Announcements</h2>
                </div>
                <a href="#" class="btn btn-outline">View All</a>
            </div>

            <div class="announcements-grid">
                <article class="announcement-card">
                    <div class="announcement-image">
                        <div class="announcement-img-placeholder">
                            <img src="<?= BASE_URL ?>/assets/images/landing_announcement_01.jpg" alt="Announcement Image" class="announcement-img">
                        </div>
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">February 4, 2026</time>
                        <h3 class="announcement-title">Red Alert Status: Tropical Cyclone Basyang (Penha)</h3>
                        <p class="announcement-description">The Provincial Disaster Risk Reduction and Management Council (PDRRMC) has placed Negros Occidental under Red Alert Status. Heavy rainfall (50–100 mm) is expected to continue through the weekend, specifically targeting southern Negros and coastal areas.</p>
                        <a href="#" class="announcement-link">Read More →</a>
                    </div>
                </article>

                <article class="announcement-card">
                    <div class="announcement-image">
                        <div class="announcement-img-placeholder">
                            <img src="<?= BASE_URL ?>/assets/images/landing_announcement_02.jpg" alt="Announcement Image" class="announcement-img">
                        </div>
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">February 6, 2026</time>
                        <h3 class="announcement-title">Province-Wide Class & Work Suspensions</h3>
                        <p class="announcement-description">Due to the severe weather conditions and the Yellow Heavy Rainfall Warning issued by PAGASA, Governor Eugenio Jose Lacson and Mayor Greg Gasataya have suspended classes at all levels in Bacolod City and several other LGUs (including Silay, Talisay, and Bago) through February 6, 2026.</p>
                        <a href="#" class="announcement-link">Read More →</a>
                    </div>
                </article>

                <article class="announcement-card">
                    <div class="announcement-image">
                        <div class="announcement-img-placeholder">
                            <img src="<?= BASE_URL ?>/assets/images/landing_announcement_03.jpg" alt="Announcement Image" class="announcement-img">
                        </div>
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">February 6, 2026</time>
                        <h3 class="announcement-title">Infrastructure Advisory: Landslide & Flash Flood Risk</h3>
                        <p class="announcement-description">The infrastructure advisory for Negros Occidental is tied to the current events this week, specifically the risks of landslides and flash floods caused by Tropical Storm Basyang (Penha), which has led to widespread work and class suspensions across the province as of today, Friday, February 6.</p>
                        <a href="#" class="announcement-link">Read More →</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA SECTION (CALL-TO-ACTION BUTTON) -->
    <section class="cta" id="contact">
        <div class="container">
            <div class="cta-content">
                <?php if ($is_user_logged_in): ?>
                    <!-- IF USER IS LOGGED IN, A DIFFERENT CTA BUTTON IS DISPLAYED -->
                    <h2 class="cta-title">Start Making a Difference Today</h2>
                    <p class="cta-description">
                        Report issues, track progress, and contribute to a better city environment.
                    </p>
                    <a href="<?= BASE_URL ?>/view/user_module/user_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <?php else: ?>
                    <!-- IF USER IS NOT LOGGED IN "Create Account Now" CTA IS DISPLAYED AS USUAL -->
                    <h2 class="cta-title">Ready to make a difference?</h2>
                    <p class="cta-description">
                        Join thousands of citizens contributing to a better city environment today.
                    </p>
                    <a href="<?= BASE_URL ?>/view/auth/index.php" class="btn btn-primary btn-lg">Create Account Now</a>
                <?php endif; ?>

                <!-- <h2 class="cta-title">Ready to make a difference?</h2> -->
                <!-- <p class="cta-description">
                    Join thousands of citizens contributing to a better city environment today.
                </p> -->
                <!-- <a href="login/index.php" class="btn btn-primary btn-lg">Create Account Now</a> -->
            </div>
        </div>
    </section>

    <script src="<?= BASE_URL ?>/assets/js/landing/main.js"></script>

    <?php require VIEW_PATH . 'partials/footer.php'; ?>
</body>

</html>