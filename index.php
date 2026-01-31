<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="stylesheet" href="agap_link/assets/css/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/view/header.php'; ?>

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
                <div class="hero-actions">
                    <a href="/agap_link/login/index.php" class="btn btn-primary btn-lg">Download App</a>
                    <a href="#" class="btn btn-secondary btn-lg">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon warning">
                         <img src="assets/icons/alert_icon.png" alt="Report Hazard">
                    </div>
                    <h3 class="feature-title">Report Hazards</h3>
                    <p class="feature-description">Spot an issue? Report it instantly and help keep our city safe.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon refresh">
                       <img src="assets/icons/refresh_icon.png" alt="Refresh Updates">
                    </div>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-description">Stay informed with live notifications about your reported issues.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon check">
                       <img src="assets/icons/check_icon.png" alt="Check Status">
                    </div>
                    <h3 class="feature-title">Track Progress</h3>
                    <p class="feature-description">Monitor the status of community projects and service requests.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
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
                           <img src="assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />
                            <span>Direct line to city services</span>
                        </li>
                        <li>
                            <img src="assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />

                            <span>Transparent tracking of issues</span>
                        </li>
                        <li>
                            <img src="assets/icons/aboutlist_icon.png" alt="About List Icon" class="about-icon" />
                            <span>Community-driven improvements</span>
                        </li>
                    </ul>
                </div>
                <div class="about-image">
                    <div class="image-wrapper">
                        <div class="image-placeholder"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
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
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">Oct. 24, 2025</time>
                        <h3 class="announcement-title">City infrastructure upgrades starting next week</h3>
                        <p class="announcement-description">Major renovations to the downtown district will begin this...</p>
                        <a href="#" class="announcement-link">Read More →</a>
                    </div>
                </article>

                <article class="announcement-card">
                    <div class="announcement-image">
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">Oct. 24, 2025</time>
                        <h3 class="announcement-title">City infrastructure upgrades starting next week</h3>
                        <p class="announcement-description">Major renovations to the downtown district will begin this...</p>
                        <a href="#" class="announcement-link">Read More →</a>
                    </div>
                </article>

                <article class="announcement-card">
                    <div class="announcement-image">
                        <span class="announcement-badge">News</span>
                    </div>
                    <div class="announcement-content">
                        <time class="announcement-date">Oct. 24, 2025</time>
                        <h3 class="announcement-title">City infrastructure upgrades starting next week</h3>
                        <p class="announcement-description">Major renovations to the downtown district will begin this...</p>
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
                <h2 class="cta-title">Ready to make a difference?</h2>
                <p class="cta-description">
                    Join thousands of citizens contributing to a better city environment today.
                </p>
                <a href="#" class="btn btn-primary btn-lg">Create Account Now</a>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/view/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>

</html>