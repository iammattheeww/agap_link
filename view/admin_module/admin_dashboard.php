<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING - CRITICAL FOR SECURITY
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

// Report MODEL INSTANCE TO FETCH ALL REPORTS FOR STATISTICS AND RECENT REPORTS DISPLAY
$reportModel = new Report();
$userReports = $reportModel->getAllReports();

$stats = [
    'total_reports'   => count($userReports),
    'resolved_count'  => count(array_filter($userReports, fn($r) => $r['status'] === 'Resolved')),
    'pending_count'   => count(array_filter($userReports, fn($r) => $r['status'] === 'Pending')),
    'ongoing_count'   => count(array_filter($userReports, fn($r) => $r['status'] === 'Ongoing'))
];

// GET USER NAME FROM SESSION
$userName = $_SESSION['admin_name'] ?? 'Admin';

// EXTRACT STATISTICS FROM ARRAY
$totalReports    = $stats['total_reports']  ?? 0;
$resolvedReports = $stats['resolved_count'] ?? 0;
$pendingReports  = $stats['pending_count']  ?? 0;

// CHECK IF REPORTS ARRAY IS EMPTY OR FALSE
$hasReports = is_array($userReports) && count($userReports) > 0;

// GROUP REPORTS BY DAY (Mon–Sun)
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$reportCounts = array_fill(0, 7, 0);

foreach ($userReports as $report) {
    if (!empty($report['created_at'])) {
        $dayIndex = date('N', strtotime($report['created_at'])) - 1;
        $reportCounts[$dayIndex]++;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>Admin Dashboard - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_module.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>       
</head>

<body>
    <?php require VIEW_PATH . 'partials/mobile_topnav_admin.php'; ?>
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">

            <!-- HEADER -->
            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h1>
                    <p class="welcome-subtitle">Here's an overview of all community reports.</p>
                </div>
                <a href="<?= BASE_URL ?>/view/admin_module/admin_report.php" class="btn-report-issue">
                    View All Reports
                </a>
            </header>

            <!-- STATISTICS CARDS -->
            <div class="stats-grid">
                <div class="stat-card stat-card-blue">
                    <div class="stat-icon-wrapper">
                        <i data-lucide="layers" class="stat-icon"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Total Reports</h3>
                        <p class="stat-value"><?= $totalReports ?></p>
                        <p class="stat-sublabel">All submissions</p>
                    </div>
                </div>

                <div class="stat-card stat-card-green">
                    <div class="stat-icon-wrapper">
                        <i data-lucide="check-circle" class="stat-icon"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Resolved</h3>
                        <p class="stat-value"><?= $resolvedReports ?></p>
                        <p class="stat-sublabel">Successfully resolved</p>
                    </div>
                </div>

                

                <div class="stat-card stat-card-orange">
                    <div class="stat-icon-wrapper">
                        <i data-lucide="clock" class="stat-icon"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Pending</h3>
                        <p class="stat-value"><?= $pendingReports ?></p>
                        <p class="stat-sublabel">Awaiting action</p>
                    </div>
                </div>
            </div>

          <section class="recent-reports-section">
    <h2 class="section-title">Reports Over Time</h2>

    <select id="filterSelect" style="margin-bottom: 20px;">
        <option value="daily">Daily</option>
        <option value="weekly" selected>Weekly</option>
        <option value="monthly">Monthly</option>
    </select>

    <div class="chart-wrapper">
        <canvas id="reportsChart"></canvas>
    </div>
</section>

            <!-- RECENT REPORTS SECTION -->
            <section class="recent-reports-section">
                <h2 class="section-title">Recent Reports</h2>

                <?php if (!$hasReports): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <p class="empty-message">No reports have been submitted yet.</p>
                    </div>
                <?php else: ?>
                    <div class="reports-list">
                        <?php foreach ($userReports as $report): ?>
                            <div class="report-card">
                                <div class="report-image-container">
                                    <?php if (!empty($report['photo_path'])): ?>
                                        <img src="<?= htmlspecialchars($report['photo_path']) ?>" alt="Report photo" class="report-image">
                                    <?php else: ?>
                                        <div class="report-placeholder">
                                            <span>&#x1F4F7;</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="report-details">
                                    <div class="report-header">
                                        <h3 class="report-title"><?= htmlspecialchars($report['category_name'] ?? 'General Report') ?></h3>
                                        <span class="status-badge status-<?= strtolower($report['status']) ?>">
                                            <?= ucfirst($report['status']) ?>
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
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>

<script>
let chart;

async function loadChart(filter = 'weekly') {
    try {
        const response = await fetch("<?= BASE_URL ?>/controller/chart_data.php?filter=" + filter);
        const result = await response.json();

        console.log("Chart Data:", result); // DEBUG

        const ctx = document.getElementById('reportsChart');

        if (!ctx) {
            console.error("Canvas not found!");
            return;
        }

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: result.labels,
                datasets: [{
                    label: 'Reports',
                    data: result.data,
                    backgroundColor: '#FF6B00',
                    borderRadius: 6
                }]
            },
          options: {
    responsive: true,
    maintainAspectRatio: false,
animation: false,
    plugins: {
        legend: { display: false }
    },

    scales: {
        x: {
            ticks: {
                maxRotation: 0,
                autoSkip: true
            },
            grid: { display: false }
        },
        y: {
            beginAtZero: true
        }
    }
}
        });

    } catch (error) {
        console.error("Chart Error:", error);
    }
}

// INITIAL LOAD
document.addEventListener("DOMContentLoaded", () => {
    loadChart();

    document.getElementById('filterSelect').addEventListener('change', function () {
        loadChart(this.value);
    });
});

window.addEventListener('resize', () => {
    loadChart(document.getElementById('filterSelect').value);
});
</script>
</body>

</html>