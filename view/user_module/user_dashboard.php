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

require MODEL_PATH . 'User.php';
require MODEL_PATH . 'Report.php';

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
<?php if (isset($_GET['report_success'])): ?>
    <div class="alert alert-success">
        Report submitted successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['report_error'])): ?>
    <div class="alert alert-error">
        <?= $_SESSION['error'] ?? 'Something went wrong.' ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>User Dashboard - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- SIDEBAR - USING REQUIRE_ONCE -->
        <?php require VIEW_PATH . 'partials/user_sidebar.php'; ?>
        <!-- MAIN CONTENT -->
       <main class="main-content page-transition">

    <!-- HEADER -->
    <header class="content-header">
        <div class="welcome-section">
            <h1 class="welcome-title">
                Welcome back, <?= htmlspecialchars($firstName) ?>!
            </h1>
            <p class="welcome-subtitle">create_report.php
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

        <?php require __DIR__ . '/create_report_form_partial.php'; ?>

    </div>
</div>

</main>
    </div>

    <script src="<?=  BASE_URL ?>/assets/js/landing/main.js"></script>

    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("reportModal");
    const openBtn = document.getElementById("openReportModal");
    const closeBtn = document.getElementById("closeReportModal");

    if (openBtn) {
        openBtn.addEventListener("click", function () {
            modal.classList.add("active");
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.classList.remove("active");
        });
    }

    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
            }
        });
    }

});
</script>
<script>
    const cancelBtn = document.getElementById("cancelReportBtn");

if (cancelBtn) {
    cancelBtn.addEventListener("click", function () {
        modal.classList.remove("active");
    });
}

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const getLocationBtn = document.getElementById("getLocationBtn");
    const status = document.getElementById("locationStatus");
    const latInput = document.getElementById("gps_lat");
    const longInput = document.getElementById("gps_long");

    if (getLocationBtn) {
        getLocationBtn.addEventListener("click", function () {

            if (!navigator.geolocation) {
                status.innerHTML = "Geolocation is not supported.";
                return;
            }

            status.innerHTML = "Getting location...";

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    latInput.value = position.coords.latitude;
                    longInput.value = position.coords.longitude;
                    status.innerHTML = "Location captured successfully.";
                },
                function () {
                    status.innerHTML = "Unable to retrieve location.";
                }
            );
        });
    }

});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const fileUploadArea = document.getElementById("fileUploadArea");
    const fileInput = document.getElementById("photo");
    const previewContainer = document.getElementById("previewContainer");
    const previewImage = document.getElementById("previewImage");
    const removeBtn = document.getElementById("removeImageBtn");

    if (fileUploadArea) {
        fileUploadArea.addEventListener("click", () => fileInput.click());
    }

    if (fileInput) {
        fileInput.addEventListener("change", function () {
            const file = this.files[0];

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = "block";
            };

            reader.readAsDataURL(file);
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener("click", function () {
            fileInput.value = "";
            previewContainer.style.display = "none";
        });
    }

});
</script>

</html>