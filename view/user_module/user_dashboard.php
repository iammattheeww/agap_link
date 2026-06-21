<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING - CRITICAL FOR SECURITY
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'Report.php';

// INITIALIZE REPORT MODEL
$reportModel = new Report();

// FETCH REPORTS FROM THE DATABASE - WE'LL USE FOREACH TO DISPLAY THEM LATER
$userReports = $reportModel->getUserReports($_SESSION['user_id']);

// FETCH STATISTICS ARRAY FROM DATABASE
$stats = $reportModel->getUserReportStats($_SESSION['user_id']);

// GET USER NAME FROM SESSION
$fullName = $_SESSION['user_name'] ?? 'User';

// Extract first name only
$firstName = explode(' ', trim($fullName))[0];


// EXTRACT STATISTICS FROM ARRAY
$totalReports = $stats['total_reports'] ?? 0;
$resolvedReports = $stats['resolved_count'] ?? 0;
$pendingReports = $stats['pending_count'] ?? 0;

// CHECK IF REPORTS ARRAY IS EMPTY OR FALSE
$hasReports = is_array($userReports) && count($userReports) > 0;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/favicon_io/favicon.ico">
    <title>User Dashboard - AGAP-Link</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/user_module/user_module.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>

<body>
    <?php require VIEW_PATH . 'partials/mobile_topnav_user.php'; ?>

    <?php if (isset($_GET['report_success'])): ?>
        <div class="alert alert-success" style="margin: 0; border-radius: 0;">
            ✅ Report submitted successfully!
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['report_error'])): ?>
        <div class="alert alert-error" style="margin: 0; border-radius: 0;">
            <?= htmlspecialchars($_SESSION['error'] ?? 'Something went wrong.') ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="dashboard-container">
        <!-- SIDEBAR - USING REQUIRE_ONCE -->
        <?php require VIEW_PATH . 'partials/user_sidebar.php'; ?> <!-- MAIN CONTENT -->

        <!-- REPORT MODAL -->
        <div class="report-modal-overlay" id="reportModal">
            <div class="report-modal">

                <div class="modal-header">
                    <h2>Report an Issue</h2>
                    <button type="button" class="modal-close" id="closeReportModal">&times;</button>
                </div>

                <p class="modal-subtitle">
                    Help improve your community by reporting issues.
                </p>

                <!-- THIS OPENS THE MODAL -->
                <?php require __DIR__ . '/create_report_form_partial.php'; ?>

            </div>
        </div>

        <main class="main-content page-transition">

            <!-- HEADER -->
            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">
                        Welcome back, <?= htmlspecialchars($firstName) ?>!
                    </h1>
                    <p class="welcome-subtitle">
                        Here's what's happening in your neighborhood.
                    </p>
                </div>

                <button type="button" class="btn-report-issue" id="openReportModal">
                    <span class="btn-icon">+</span>
                    Report Issue
                </button>
            </header>


            <!-- STATISTICS -->
            <section class="stats-grid">

                <!-- ORANGE MAIN CARD -->
                <div class="stat-card stat-card-main">
                    <div class="stat-content">
                        <h3 class="stat-label">My Reports</h3>
                        <p class="stat-value"><?= $totalReports ?></p>
                        <p class="stat-sublabel">Total submissions</p>
                    </div>
                </div>

                <!-- RESOLVED -->
                <div class="stat-card stat-card-light stat-card-green">
                    <div class="stat-content">
                        <h3 class="stat-label">Resolved</h3>
                        <p class="stat-value"><?= $resolvedReports ?></p>
                        <p class="stat-sublabel">Issues fixed</p>
                    </div>
                </div>

                <!-- PENDING -->
                <div class="stat-card stat-card-light stat-card-orange">
                    <div class="stat-content">
                        <h3 class="stat-label">Pending</h3>
                        <p class="stat-value"><?= $pendingReports ?></p>
                        <p class="stat-sublabel">In progress</p>
                    </div>
                </div>

            </section>


            <!-- RECENT ACTIVITY -->
            <section class="recent-reports-section">

                <h2 class="section-title">Recent Activity</h2>

                <?php if (!$hasReports): ?>

                    <div class="empty-state">
                        <div class="empty-icon">!</div>
                        <p class="empty-message">
                            You haven't submitted any reports yet.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="reports-list-activity">

                        <?php foreach ($userReports as $report): ?>

                            <div class="activity-card">

                                <!-- STATUS ICON -->
                                <div class="activity-icon 
                            <?= strtolower($report['status']) === 'resolved' ? 'icon-success' : 'icon-pending' ?>">

                                    <?= strtolower($report['status']) === 'resolved' ? '✓' : '⏱' ?>
                                </div>

                                <!-- DETAILS -->
                                <div class="activity-content">
                                    <h3 class="activity-title">
                                        <?= htmlspecialchars($report['description']) ?>
                                    </h3>

                                    <div class="activity-meta">
                                        <span>📍 <?= htmlspecialchars($report['address']) ?></span>
                                        <span>• <?= date('Y-m-d', strtotime($report['created_at'])) ?></span>
                                    </div>
                                </div>

                                <!-- BADGE -->
                                <span class="status-badge status-<?= strtolower($report['status']) ?>">
                                    <?= strtolower($report['status']) ?>
                                </span>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/user_module/main.js"></script>
</body>



</html>