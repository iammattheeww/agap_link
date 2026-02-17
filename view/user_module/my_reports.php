    <?php
    require_once dirname(__DIR__, 2) . "/config/init.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/view/auth/index.php");
        exit();
    }

    require_once __DIR__ . '/../../model/Report.php';

    $reportModel = new Report();
    $userReports = $reportModel->getUserReports($_SESSION['user_id']);
    $userName = $_SESSION['user_name'] ?? 'User';
    $hasReports = is_array($userReports) && count($userReports) > 0;
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>My Reports - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
    </head>

    <body>
    <div class="dashboard-container">

    <?php require_once __DIR__ . '/../partials/user_sidebar.php'; ?>

    <div class="page-transition">
    <main class="main-content">

        <!-- HEADER -->
        <div class="content-header">
            <div>
                <h1 class="page-title">My Reports</h1>
                <p class="welcome-subtitle">Track the status of issues you've reported.</p>
            </div>

            <div class="reports-toolbar">
                <div class="reports-search">
                    <input type="text" id="reportSearch" placeholder="Search reports..." class="form-input">
                </div>

                <div class="reports-filters">
        <div class="filter-dropdown" id="statusDropdown">
            <button class="filter-toggle" id="filterToggle">
                 <span id="selectedFilter"></span> 
            </button>

            <div class="filter-menu">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="ongoing">Ongoing</button>
                <button class="filter-btn" data-filter="resolved">Resolved</button>
            </div>
        </div>
    </div>

            </div>
        </div>

        <!-- ALERTS -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- REPORT LIST -->
        <section class="recent-reports-section">

            <?php if (!$hasReports): ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p class="empty-message">You haven't submitted any reports yet.</p>
                    <button class="btn-submit-first"
                            onclick="window.location.href='<?= BASE_URL ?>/view/user_module/user_dashboard.php'">
                        <span class="btn-icon">+</span>
                        Submit your first report
                    </button>
                </div>
            <?php else: ?>

            <div class="reports-list-activity">

            <?php foreach ($userReports as $report):

                // normalize status for JS filter
                $status = strtolower(trim($report['status']));
                if ($status === "in progress") $status = "ongoing";

                $statusClass = "status-$status";

                // icon color by status
                $iconClass = "icon-pending";
                if ($status === "resolved") $iconClass = "icon-success";
                if ($status === "ongoing") $iconClass = "icon-pending";

            ?>

            <div class="report-card"
        data-id="<?= $report['report_id'] ?>"
        data-status="<?= $status ?>"
        data-category="<?= htmlspecialchars($report['category_name']) ?>"
        data-address="<?= htmlspecialchars($report['address']) ?>"
        data-date="<?= date('F d, Y', strtotime($report['created_at'])) ?>"
        data-status-text="<?= strtoupper($status) ?>">

        <!-- LEFT ICON -->
        <div class="activity-icon <?= $iconClass ?>">
            <?= strtoupper(substr($report['category_name'] ?? 'R', 0, 1)) ?>
        </div>

        <!-- CENTER TEXT -->
        <div class="report-content">
            <div class="activity-title">
                <?= htmlspecialchars($report['category_name'] ?? 'Report') ?>
            </div>

            <div class="activity-meta">
                <span>📍 <?= htmlspecialchars($report['address']) ?></span>
                <span>•</span>
                <span><?= date('Y-m-d', strtotime($report['created_at'])) ?></span>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="report-right">
            <span class="status-badge <?= $statusClass ?>">
                <?= strtolower($status) ?>
            </span>
            <span class="activity-arrow">›</span>
        </div>

    </div>


            <?php endforeach; ?>

            </div>
            <?php endif; ?>

        </section>

    </main>
    </div>
    </div>

    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <script src="<?= ASSET_URL ?>/js/user_module/reports.js"></script>

    <!-- REPORT MODAL -->
    <div id="reportModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
        <h3 id="modalCategory">Report</h3>
        <button class="modal-close" id="closeModal">×</button>
        </div>

        <div class="modal-body">
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Location:</strong> <span id="modalAddress"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        </div>
    </div>
    </div>

    </body>

    <script> 
        // OPEN MODAL WHEN CARD CLICKED
    document.querySelectorAll(".report-card").forEach(card => {
    card.addEventListener("click", function () {

        const modal = document.getElementById("reportModal");

        document.getElementById("modalCategory").textContent =
        this.dataset.category;

        document.getElementById("modalStatus").textContent =
        this.dataset.statusText;

        document.getElementById("modalAddress").textContent =
        this.dataset.address;

        document.getElementById("modalDate").textContent =
        this.dataset.date;

        modal.style.display = "flex";
    });
    });

    // CLOSE MODAL
    document.getElementById("closeModal").addEventListener("click", () => {
    document.getElementById("reportModal").style.display = "none";
    });

    // CLOSE OUTSIDE CLICK
    document.getElementById("reportModal").addEventListener("click", e => {
    if (e.target.id === "reportModal") {
        e.currentTarget.style.display = "none";
    }
    });

    </script>
    </html>
