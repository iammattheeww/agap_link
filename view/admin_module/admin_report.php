<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

$reportModel = new Report();
$allReports  = $reportModel->getAllReports();
$hasReports  = is_array($allReports) && count($allReports) > 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>Reports Management - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_module.css">
</head>

<body>

<div class="dashboard-container">

    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="main-content">

        <!-- HEADER -->
        <div class="reports-header">
            <div>
                <h1>Reports Management</h1>
                <p>Review, manage, and forward community reports to LGUs.</p>
            </div>
            <div class="reports-actions">
                <button class="btn-export">&#x2B07; Export</button>
                <button class="btn-filter">&#x25BC; Filter</button>
            </div>
        </div>

        <!-- SEARCH -->
        <div class="reports-search">
            <input type="text" placeholder="Search reports by issue, status, or location...">
        </div>

        <!-- REPORTS TABLE -->
        <section class="reports-section">
            <?php if (!$hasReports): ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p class="empty-message">No reports submitted yet.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Issue</th>
                                <th>Reporter</th>
                                <th>Status</th>
                                <th>Forwarded To</th>
                                <th>Forward Action</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allReports as $report): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($report['report_id']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($report['category_name'] ?? 'General') ?>
                                        <span class="report-cell-sub">
                                            <?= htmlspecialchars(mb_strimwidth($report['description'] ?? '', 0, 60, '…')) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($report['full_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        $status = strtolower($report['status']);
                                        $statusClass = match($status) {
                                            'pending'     => 'status-pending',
                                            'in progress' => 'status-in-progress',
                                            'ongoing'     => 'status-ongoing',
                                            'verified'    => 'status-verified',
                                            'forwarded'   => 'status-forwarded',
                                            'resolved'    => 'status-resolved',
                                            default       => 'status-pending'
                                        };
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($report['agency_name'] ?? '—') ?></td>
                                    <td>
                                        <select>
                                            <option value="">Select LGU</option>
                                            <option>Public Works Department</option>
                                            <option>Waste Management Office</option>
                                            <option>Police & Public Safety</option>
                                            <option>Environment & Natural Resources</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a href="view_report.php?id=<?= $report['report_id'] ?>" class="btn-action">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
<script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>

</body>
</html>