<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/Report.php';

$reportModel = new Report();
$userReports = $reportModel->getUserReports($_SESSION['user_id']);
$hasReports  = is_array($userReports) && count($userReports) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>My Reports - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
    <?php require VIEW_PATH . 'partials/mobile_topnav_user.php'; ?>

    <div class="dashboard-container">
        <?php require_once __DIR__ . '/../partials/user_sidebar.php'; ?>

      <div class="report-modal-overlay" id="createReportModal">
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

        <main class="main-content page-transition">
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
                                <span id="selectedFilter">▼</span>
                            </button>
                            <div class="filter-menu">
                                <button class="filter-btn active" data-filter="all">All</button>
                                <button class="filter-btn" data-filter="pending">Pending</button>
                                <button class="filter-btn" data-filter="ongoing">Ongoing</button>
                                <button class="filter-btn" data-filter="resolved">Resolved</button>
                            </div>
                        </div>
                    </div>
                       <button type="button" class="btn-report-issue" id="openReportModal">
                    <span class="btn-icon">+</span>
                    Report Issue
                </button>
                </div>
            </div>

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
                            $status = strtolower(trim($report['status']));
                            if ($status === "in progress") $status = "ongoing";
                            $statusClass = "status-$status";
                            $iconClass = ($status === "resolved") ? "icon-success" : "icon-pending";
                        ?>
                            <div class="report-card"
                                data-id="<?= $report['report_id'] ?>"
                                data-status="<?= $status ?>"
                                data-category="<?= htmlspecialchars($report['category_name']) ?>"
                                data-address="<?= htmlspecialchars($report['address']) ?>"
                                data-date="<?= date('F d, Y', strtotime($report['created_at'])) ?>"
                                data-status-text="<?= strtoupper($status) ?>"
                                data-description="<?= htmlspecialchars($report['description']) ?>">

                                <div class="activity-icon <?= $iconClass ?>">
                                    <?= strtoupper(substr($report['category_name'] ?? 'R', 0, 1)) ?>
                                </div>

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

                                <div class="report-right">
                                    <span class="status-badge <?= $statusClass ?>"><?= strtolower($status) ?></span>
                                    <span class="activity-arrow">›</span>
                                </div>

                                <!-- DELETE REPORT BUTTON -->
                                <form method="POST"
                                      action="<?= BASE_URL ?>/controller/delete_report.php"
                                      class="delete-report-form"
                                      onsubmit="return confirm('Are you sure you want to delete this report? This cannot be undone.');">
                                    <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                    <button type="submit" class="btn-delete-report" title="Delete Report">🗑</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- REPORT DETAIL MODAL -->
  <div id="reportDetailModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalCategory">Report</h3>
                <button class="modal-close" id="closeModal">×</button>
            </div>
            <div class="modal-body">
                <div class="report-info">
                    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                    <p><strong>Location:</strong> <span id="modalAddress"></span></p>
                    <p><strong>Date Submitted:</strong> <span id="modalDate"></span></p>
                    <p><strong>Description:</strong></p>
                    <div id="modalDescription" class="report-description"></div>
                </div>
                <div class="timeline">
                    <div class="timeline-item" data-step="1">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Report Submitted</div>
                            <div class="timeline-date" id="timelineSubmitted"></div>
                        </div>
                    </div>
                    <div class="timeline-item" data-step="2">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Under Review / Ongoing</div>
                            <div class="timeline-date" id="timelineOngoing"></div>
                        </div>
                    </div>
                    <div class="timeline-item" data-step="3">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Resolved</div>
                            <div class="timeline-date" id="timelineResolved"></div>
                        </div>
                    </div>
                    <div class="timeline-progress">
                        <div class="timeline-progress-fill" id="timelineProgress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <script src="<?= ASSET_URL ?>/js/user_module/reports.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>

    document.addEventListener("DOMContentLoaded", function() {

       const modal = document.getElementById("createReportModal");
        const openBtn = document.getElementById("openReportModal");
        const closeBtn = document.getElementById("closeReportModal");

        if (openBtn) {
            openBtn.addEventListener("click", function() {
                modal.classList.add("active");
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener("click", function() {
                modal.classList.remove("active");
            });
        }

        if (modal) {
            modal.addEventListener("click", function(e) {
                if (e.target === modal) {
                    modal.classList.remove("active");
                }
            });
        }

    });
</script>
<script>
   const cancelBtn = document.getElementById("cancelReportBtn");
const createModal = document.getElementById("createReportModal");

if (cancelBtn && createModal) {
    cancelBtn.addEventListener("click", function() {
        createModal.classList.remove("active");
    });
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const getLocationBtn = document.getElementById("getLocationBtn");
        const status = document.getElementById("locationStatus");
        const latInput = document.getElementById("gps_lat");
        const longInput = document.getElementById("gps_long");

        if (getLocationBtn) {
            getLocationBtn.addEventListener("click", function() {

                if (!navigator.geolocation) {
                    status.innerHTML = "Geolocation is not supported.";
                    return;
                }

                status.innerHTML = "Getting location...";

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latInput.value = position.coords.latitude;
                        longInput.value = position.coords.longitude;
                        status.innerHTML = "Location captured successfully.";
                    },
                    function() {
                        status.innerHTML = "Unable to retrieve location.";
                    }
                );
            });
        }

    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const fileUploadArea = document.getElementById("fileUploadArea");
        const fileInput = document.getElementById("photo");
        const previewContainer = document.getElementById("previewContainer");
        const previewImage = document.getElementById("previewImage");
        const removeBtn = document.getElementById("removeImageBtn");

        if (fileUploadArea) {
            fileUploadArea.addEventListener("click", () => fileInput.click());
        }

        if (fileInput) {
            fileInput.addEventListener("change", function() {
                const file = this.files[0];

                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = "block";
                };

                reader.readAsDataURL(file);
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener("click", function() {
                fileInput.value = "";
                previewContainer.style.display = "none";
            });
        }

    });
</script>
</body>
</html>
