<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /agap_link/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/Report.php';

$reportModel = new Report();
$allReports = $reportModel->getAllReports();
$hasReports = is_array($allReports) && count($allReports) > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Reports - Admin | AGAP-Link</title>
    <link rel="stylesheet" href="/agap_link/assets/css/admin_module/admin_module.css">
</head>

<body>

<div class="dashboard-container">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="main-content">

        <!-- PAGE HEADER -->
        <div class="reports-header">
            <h1 class="page-title">All Submitted Reports</h1>
        </div>

        <?php if (!$hasReports): ?>

            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p class="empty-message">No reports have been submitted yet.</p>
            </div>

        <?php else: ?>

            <!-- REPORTS LIST -->
            <div class="reports-list">

                <?php foreach ($allReports as $report): ?>

                    <div class="report-card">

                        <!-- IMAGE -->
                        <div class="report-image-container">
                            <?php if (!empty($report['photo_path'])): ?>
                                <img src="<?= htmlspecialchars($report['photo_path']) ?>" 
                                     class="report-image">
                            <?php else: ?>
                                <div class="report-placeholder">📷</div>
                            <?php endif; ?>
                        </div>

                        <!-- DETAILS -->
                        <div class="report-details">

                            <div class="report-header">
                                <h3 class="report-title">
                                    <?= htmlspecialchars($report['category_name'] ?? 'General Report') ?>
                                </h3>

                                <span class="status-badge status-<?= strtolower($report['status']) ?>">
                                    <?= strtoupper($report['status']) ?>
                                </span>
                            </div>

                            <p>
                                <strong>Reporter:</strong>
                                <?= htmlspecialchars($report['reporter_name']) ?>
                            </p>

                            <p class="report-description">
                                <?= htmlspecialchars($report['description']) ?>
                            </p>

                            <div class="report-meta">
                                <div class="meta-item">
                                    <span class="meta-icon">📍</span>
                                    <?= htmlspecialchars($report['address']) ?>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-icon">🕒</span>
                                    <?= date('M d, Y h:i A', strtotime($report['created_at'])) ?>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-icon">🏢</span>
                                    <?= htmlspecialchars($report['agency_name'] ?? 'Not Assigned') ?>
                                </div>
                            </div>

                            <!-- ACTION BUTTON -->
                            <div class="report-actions">
                                <a href="view_report.php?id=<?= $report['report_id'] ?>" 
                                   class="btn-action btn-edit">
                                    👁 View Details
                                </a>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>
