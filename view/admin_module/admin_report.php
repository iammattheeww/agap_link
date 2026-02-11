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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports Management - Admin | AGAP-Link</title>
   <link rel="stylesheet" href="/agap_link/assets/css/admin_module/admin_module.css">
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
                <button class="btn-export">Export</button>
                <button class="btn-filter">Filter</button>
            </div>
        </div>

        <!-- SEARCH -->
        <div class="reports-search">
            <input type="text" placeholder="Search reports...">
        </div>

        <?php if (!$hasReports): ?>
            <div class="empty-state" style="text-align:center; padding:50px; color:var(--color-gray-600);">
                No reports submitted yet.
            </div>
        <?php else: ?>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Issue</th>
                        <th>Status</th>
                        <th>Forwarded To</th>
                        <th>Forward Action</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allReports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['report_id']) ?></td>
                            <td>
                                <?= htmlspecialchars($report['category_name'] ?? 'General') ?>
                                <br>
                                <span style="font-size:0.85rem; color:var(--color-gray-600);">
                                    <?= htmlspecialchars($report['description'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = strtolower($report['status']);
                                $statusClass = match($status) {
                                    'pending' => 'status-pending',
                                    'in progress' => 'status-in-progress',
                                    'resolved' => 'status-resolved',
                                    default => 'status-pending'
                                };
                                ?>
                                <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= htmlspecialchars($report['agency_name'] ?? 'Not yet forwarded') ?></td>
                            <td>
                                <select>
                                    <option>Select LGU</option>
                                    <option>Public Works Department</option>
                                    <option>Waste Management Office</option>
                                    <option>Police & Public Safety</option>
                                </select>
                            </td>
                            <td>
                                <a href="view_report.php?id=<?= $report['report_id'] ?>" class="btn-action">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
