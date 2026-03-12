<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// CHECK IF USER IS LOGGED IN
$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

// CAPTURE SUCCESS/ERROR MESSAGES (For logout messages)
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch 3 most recent announcements via model
require_once MODEL_PATH . 'Announcement.php';
$latestAnnouncements = [];
try {
    $announcementModel   = new Announcement();
    $latestAnnouncements = $announcementModel->getLatest(3);
} catch (Exception $e) {
    // Table may not exist yet; silently fail — hardcoded fallback below
}
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

                <div class="hero-actions">
                    <?php if ($is_user_logged_in): ?>
                        <a href="<?= BASE_URL ?>/view/user_module/user_dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        <a href="#services" class="btn btn-secondary btn-lg">Learn More</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/view/auth/index.php" class="btn btn-primary btn-lg">Report Incident</a>
                        <a href="#services" class="btn btn-secondary btn-lg">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon warning">
                        <img src="<?= ASSET_URL ?>/icons/alert_icon.png" alt="Report Hazard">
                    </div>
                    <h3 class="feature-title">Report Hazards</h3>
                    <p class="feature-description">Spot an issue? Report it instantly and help keep our city safe.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon refresh">
                        <img src="<?= ASSET_URL ?>/icons/refresh_icon.png" alt="Refresh Updates">
                    </div>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-description">Stay informed with live notifications about your reported issues.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon check">
                        <img src="<?= ASSET_URL ?>/icons/check_icon.png" alt="Check Status">
                    </div>
                    <h3 class="feature-title">Track Progress</h3>
                    <p class="feature-description">Monitor the status of community projects and service requests.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about" id="services">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-label">WHO WE ARE</span>
                    <h2 class="section-title">About Agap-Link</h2>
                    <p class="about-text">
                        AGAP-Link bridges the gap between citizens and city administration in disaster-prone communities.
                        Our platform is purpose-built for emergency response — giving every resident a direct line to report
                        hazards, coordinate with agencies like the BFP and PNP, and stay informed during critical situations.
                    </p>
                    <p class="about-text" style="margin-top: 12px;">
                        Whether it's a flooded road, a downed power line, or an active fire threat, AGAP-Link makes sure
                        your report reaches the right unit — fast. Here's what the platform does for you:
                    </p>
                    <ul class="about-list">
                        <li>
                            <img src="<?= ASSET_URL ?>/icons/alert_icon.png" alt="Hazard Reporting" class="about-icon" />
                            <span><strong>Hazard Reporting</strong> — Submit incidents with photos and GPS-pinned locations. No street address needed — just drop a map pin.</span>
                        </li>
                        <li>
                            <img src="<?= ASSET_URL ?>/icons/refresh_icon.png" alt="Real-Time Updates" class="about-icon" />
                            <span><strong>Real-Time SMS Updates</strong> — Receive automatic text notifications every time your report's status changes, from submission to resolution.</span>
                        </li>
                        <li>
                            <img src="<?= ASSET_URL ?>/icons/check_icon.png" alt="Progress Tracking" class="about-icon" />
                            <span><strong>Progress Tracking</strong> — Monitor the live status of every report you file — Pending, Verified, Forwarded, Ongoing, or Resolved.</span>
                        </li>
                        <li>
                            <img src="<?= ASSET_URL ?>/icons/aboutlist_icon.png" alt="Agency Coordination" class="about-icon" />
                            <span><strong>Agency Forwarding</strong> — Verified reports are automatically routed to the appropriate response unit (BFP, PNP, DRRMO, etc.).</span>
                        </li>
                        <li>
                            <img src="<?= ASSET_URL ?>/icons/aboutlist_icon.png" alt="Community Announcements" class="about-icon" />
                            <span><strong>Community Announcements</strong> — Stay updated with official advisories, alerts, and news published directly by city administrators.</span>
                        </li>
                    </ul>
                </div>

                <div class="about-image">
                    <div class="image-wrapper">
                        <div class="image-placeholder">
                            <img src="<?= ASSET_URL ?>/images/landing_about.jpg" alt="About Agap-Link" class="about-img">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ANNOUNCEMENTS — Dynamic from DB, fallback to hardcoded if empty -->
    <section class="announcements" id="announcements">
        <div class="container">
            <div class="announcements-header">
                <div>
                    <span class="section-label">STAY UPDATED</span>
                    <h2 class="section-title">Latest Announcements</h2>
                </div>
                <a href="<?= BASE_URL ?>/view/landing_module/announcements.php" class="btn btn-outline">View All</a>
            </div>

            <div class="announcements-grid">

                <?php if (!empty($latestAnnouncements)): ?>
                    <?php foreach ($latestAnnouncements as $ann): ?>
                        <article class="announcement-card">
                            <div class="announcement-image">
                                <div class="announcement-img-placeholder">
                                    <?php if (!empty($ann['image_path'])): ?>
                                        <img src="<?= ASSET_URL . '/../uploads/announcements/' . htmlspecialchars(basename($ann['image_path'])) ?>"
                                             alt="Announcement Image" class="announcement-img">
                                    <?php else: ?>
                                        <img src="<?= ASSET_URL ?>/images/landing_announcement_01.jpg"
                                             alt="Announcement Image" class="announcement-img">
                                    <?php endif; ?>
                                </div>
                                <span class="announcement-badge">News</span>
                            </div>
                            <div class="announcement-content">
                                <time class="announcement-date"><?= date('F j, Y', strtotime($ann['created_at'])) ?></time>
                                <h3 class="announcement-title"><?= htmlspecialchars($ann['title']) ?></h3>
                                <p class="announcement-description">
                                    <?= htmlspecialchars(mb_strimwidth($ann['content'], 0, 120, '…')) ?>
                                </p>
                                <a href="<?= BASE_URL ?>/view/landing_module/announcements.php" class="announcement-link">Read More →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                <?php else: ?>
                    <!-- Fallback: hardcoded announcements shown when DB is empty -->
                    <article class="announcement-card">
                        <div class="announcement-image">
                            <div class="announcement-img-placeholder">
                                <img src="<?= ASSET_URL ?>/images/landing_announcement_01.jpg" alt="Announcement Image" class="announcement-img">
                            </div>
                            <span class="announcement-badge">News</span>
                        </div>
                        <div class="announcement-content">
                            <time class="announcement-date">February 4, 2026</time>
                            <h3 class="announcement-title">Red Alert Status: Tropical Cyclone Basyang (Penha)</h3>
                            <p class="announcement-description">The Provincial Disaster Risk Reduction and Management Council (PDRRMC) has placed Negros Occidental under Red Alert Status.</p>
                            <a href="#" class="announcement-link">Read More →</a>
                        </div>
                    </article>

                    <article class="announcement-card">
                        <div class="announcement-image">
                            <div class="announcement-img-placeholder">
                                <img src="<?= ASSET_URL ?>/images/landing_announcement_02.jpg" alt="Announcement Image" class="announcement-img">
                            </div>
                            <span class="announcement-badge">News</span>
                        </div>
                        <div class="announcement-content">
                            <time class="announcement-date">February 6, 2026</time>
                            <h3 class="announcement-title">Province-Wide Class & Work Suspensions</h3>
                            <p class="announcement-description">Due to severe weather conditions and a Yellow Heavy Rainfall Warning, classes and work have been suspended at all levels in Bacolod City.</p>
                            <a href="#" class="announcement-link">Read More →</a>
                        </div>
                    </article>

                    <article class="announcement-card">
                        <div class="announcement-image">
                            <div class="announcement-img-placeholder">
                                <img src="<?= ASSET_URL ?>/images/landing_announcement_03.jpg" alt="Announcement Image" class="announcement-img">
                            </div>
                            <span class="announcement-badge">News</span>
                        </div>
                        <div class="announcement-content">
                            <time class="announcement-date">February 6, 2026</time>
                            <h3 class="announcement-title">Infrastructure Advisory: Landslide & Flash Flood Risk</h3>
                            <p class="announcement-description">Risks of landslides and flash floods caused by Tropical Storm Basyang have led to widespread work and class suspensions across the province.</p>
                            <a href="#" class="announcement-link">Read More →</a>
                        </div>
                    </article>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <section class="cta" id="contact">
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
                    <a href="<?= BASE_URL ?>/view/auth/index.php" class="btn btn-primary btn-lg">Report Now</a>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <script src="<?= ASSET_URL ?>/js/landing/main.js"></script>

    <?php require VIEW_PATH . 'partials/footer.php'; ?>
</body>

</html>
