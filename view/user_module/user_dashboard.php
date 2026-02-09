<?php
session_start();

// PREVENT BROWSER CACHING - CRITICAL FOR SECURITY
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: /agap_link/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/Report.php';

// INITIALIZE REPORT MODEL
$reportModel = new Report();

// FETCH REPORTS FROM THE DATABASE - WE'LL USE FOREACH TO DISPLAY THEM LATER
$userReports = $reportModel->getUserReports($_SESSION['user_id']);

// FETCH STATISTICS ARRAY FROM DATABASE
$stats = $reportModel->getUserReportStats($_SESSION['user_id']);

// GET USER NAME FROM SESSION
$userName = $_SESSION['user_name'] ?? 'User';

// EXTRACT STATISTICS FROM ARRAY
$totalReports = $stats['total'] ?? 0;
$resolvedReports = $stats['resolved'] ?? 0;
$pendingReports = $stats['pending'] ?? 0;

// CHECK IF REPORTS ARRAY IS EMPTY OR FALSE
$hasReports = is_array($userReports) && count($userReports) > 0;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/agap_link/assets/favicon_io/favicon.ico">
    <title>User Dashboard - AGAP-Link</title>
    <link rel="stylesheet" href="/agap_link/assets/css/user_module/user_module.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- SIDEBAR - USING REQUIRE_ONCE -->
        <?php require_once __DIR__ . '/../partials/user_sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- HEADER -->
            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($userName) ?>!</h1>
                    <p class="welcome-subtitle">Here's what's happening in your neighborhood.</p>
                </div>
                <a href="/agap_link/view/user_module/create_report.php" class="btn-report-issue">
                    <span class="btn-icon">+</span>
                    Report Issue
                </a>
            </header>

            <!-- STATISTICS CARDS -->
            <div class="stats-grid">
                <div class="stat-card stat-card-blue">
                    <div class="stat-content">
                        <h3 class="stat-label">My Reports</h3>
                        <p class="stat-value"><?= $totalReports ?></p>
                        <p class="stat-sublabel">Total Submissions</p>
                    </div>
                </div>

                <div class="stat-card stat-card-green">
                    <div class="stat-content">
                        <h3 class="stat-label">Resolved</h3>
                        <p class="stat-value"><?= $resolvedReports ?></p>
                        <p class="stat-sublabel">In progress</p>
                    </div>
                </div>

                <div class="stat-card stat-card-orange">
                    <div class="stat-content">
                        <h3 class="stat-label">Pending</h3>
                        <p class="stat-value"><?= $pendingReports ?></p>
                        <p class="stat-sublabel">In progress</p>
                    </div>
                </div>
            </div>

            <!-- RECENT REPORTS SECTION -->
            <section class="recent-reports-section">
                <h2 class="section-title">Recent Reports</h2>

                <?php if (!$hasReports): ?>
                    <!-- EMPTY STATE - NO REPORTS IN ARRAY -->
                    <div class="empty-state">
                        <div class="empty-icon">!</div>
                        <p class="empty-message">You haven't submitted any reports yet.</p>
                        <a href="/agap_link/view/user_module/create_report.php" class="btn-submit-first">
                            <span class="btn-icon">+</span>
                            Submit your first report
                        </a>
                    </div>
                <?php else: ?>
                    <!-- REPORTS LIST - USING FOREACH LOOP TO ITERATE THROUGH ARRAY -->
                    <div class="reports-list">
                        <?php
                        // LOOP THROUGH EACH REPORT IN THE ARRAY
                        foreach ($userReports as $index => $report):
                        ?>
                            <div class="report-card">
                                <div class="report-image-container">
                                    <?php if (!empty($report['photo_path'])): ?>
                                        <img src="<?= htmlspecialchars($report['photo_path']) ?>" alt="Report photo" class="report-image">
                                    <?php else: ?>
                                        <!-- PLACEHOLDER IF NO IMAGE IN ARRAY -->
                                        <div class="report-placeholder">
                                            <span class="placeholder-icon">&#x1F4F7;</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="report-details">
                                    <div class="report-header">
                                        <!-- DISPLAY DATA FROM CURRENT ARRAY ELEMENT -->
                                        <h3 class="report-title"><?= htmlspecialchars($report['category_name'] ?? 'General Report') ?></h3>
                                        <span class="status-badge status-<?= strtolower($report['status']) ?>">
                                            <?= strtoupper($report['status']) ?>
                                        </span>
                                    </div>
                                    <p class="report-description"><?= htmlspecialchars($report['description']) ?></p>
                                    <div class="report-meta">
                                        <span class="meta-item">
                                            <span class="meta-icon">&#x1F4CD;</span>
                                            <?= htmlspecialchars($report['address']) ?>
                                        </span>
                                        <span class="meta-item">
                                            <span class="meta-icon">&#x1F551;</span>
                                            <?= date('M. d, Y', strtotime($report['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endforeach;
                        // END OF FOREACH LOOP
                        ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="/agap_link/assets/js/user_module/main.js"></script>

    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

</html>