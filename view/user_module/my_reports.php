<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING - CRITICAL FOR SECURITY
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/Report.php';

// INITIALIZE REPORT MODEL
$reportModel = new Report();

// FETCH ALL USER REPORTS
$userReports = $reportModel->getUserReports($_SESSION['user_id']);

// GET USER NAME FROM SESSION
$userName = $_SESSION['user_name'] ?? 'User';

// CHECK IF REPORTS ARRAY IS EMPTY
$hasReports = is_array($userReports) && count($userReports) > 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>My Reports - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once __DIR__ . '/../partials/user_sidebar.php'; ?>

        <main class="main-content">
            <div class="reports-header">
                <h1 class="page-title">My Reports</h1>
                <div class="reports-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="verified">Verified</button>
                    <button class="filter-btn" data-filter="ongoing">Ongoing</button>
                    <button class="filter-btn" data-filter="resolved">Resolved</button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <section class="recent-reports-section">
                <?php if (!$hasReports): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <p class="empty-message">You haven't submitted any reports yet.</p>
                        <button class="btn-submit-first" onclick="window.location.href='<?= BASE_URL ?>/view/user_module/user_dashboard.php'">
                            <span class="btn-icon">+</span>
                            Submit your first report
                        </button>
                    </div>
                <?php else: ?>
                    <div class="reports-list">
                        <?php foreach ($userReports as $report): ?>
                            <div class="report-card" data-status="<?= strtolower($report['status']) ?>">
                                <div class="report-image-container">
                                    <?php if (!empty($report['photo_path'])): ?>
                                        <img src="<?= htmlspecialchars($report['photo_path']) ?>" alt="Report photo" class="report-image">
                                    <?php else: ?>
                                        <div class="report-placeholder">
                                            <span class="placeholder-icon">📷</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="report-details">
                                    <div class="report-header">
                                        <h3 class="report-title"><?= htmlspecialchars($report['category_name'] ?? 'General Report') ?></h3>
                                        <span class="status-badge status-<?= strtolower($report['status']) ?>">
                                            <?= strtoupper($report['status']) ?>
                                        </span>
                                    </div>
                                    <p class="report-description"><?= htmlspecialchars($report['description']) ?></p>
                                    <div class="report-meta">
                                        <span class="meta-item">
                                            <span class="meta-icon">📍</span>
                                            <?= htmlspecialchars($report['address']) ?>
                                        </span>
                                        <span class="meta-item">
                                            <span class="meta-icon">🕐</span>
                                            <?= date('M. d, Y', strtotime($report['created_at'])) ?>
                                        </span>
                                        <?php if (!empty($report['priority'])): ?>
                                            <span class="meta-item">
                                                <span class="meta-icon">⚠️</span>
                                                <?= htmlspecialchars($report['priority']) ?> Priority
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($report['status'] === 'Pending'): ?>
                                        <div class="report-actions">
                                            <button class="btn-action btn-edit" onclick="editReport(<?= $report['report_id'] ?>)">
                                                ✏️ Edit
                                            </button>
                                            <button class="btn-action btn-delete" onclick="confirmDelete(<?= $report['report_id'] ?>)">
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <script src="<?= ASSET_URL ?>/js/user_module/reports.js"></script>
    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

</html>