<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../agap_link/assets/css/style.css">
</head>

<body>
    <?php require_once '../agap_link/view/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
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
                    <h1>Hello, Agap City!</h1>
                </h1>
                <p class="hero-description">
                    Your ultimate city companion! Access services, report issues, and stay connected with your community in real-time.
                </p>
                <div class="hero-actions">
                    <a href="#" class="btn btn-primary btn-lg">Download App</a>
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
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Report Hazards</h3>
                    <p class="feature-description">Spot an issue? Report it instantly and help keep our city safe.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon refresh">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10" />
                            <polyline points="1 20 1 14 7 14" />
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-description">Stay informed with live notifications about your reported issues.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon check">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
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
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            <span>Direct line to city services</span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            <span>Transparent tracking of issues</span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
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

    <!-- CTA Section -->
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

    <?php require_once '../agap_link/view/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>

</html>