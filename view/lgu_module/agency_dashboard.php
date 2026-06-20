<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Check if agency is logged in
if (!isset($_SESSION['agency_logged_in']) || $_SESSION['agency_logged_in'] !== true) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

$agency_id      = $_SESSION['agency_id'];
$agency_name    = $_SESSION['agency_name'];
$agency_user_id = $_SESSION['agency_user_id'];

// Filter parameters
$filterStatus = $_GET['status'] ?? '';
$filterSearch  = trim($_GET['search'] ?? '');

$reportModel = new Report();
$agencyReports = $reportModel->getAgencyReports($agency_id, $filterStatus, $filterSearch);
$hasReports   = !empty($agencyReports);

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$statuses = ['Pending', 'Verified', 'Forwarded', 'Ongoing', 'Resolved'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title><?= htmlspecialchars($agency_name) ?> - AGAP-Link Agency Dashboard</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/agency_module/agency_module.css">
</head>

<body data-base-url="<?= BASE_URL ?>">
    <?php require VIEW_PATH . 'partials/mobile_topnav_admin.php'; ?>

    <div class="dashboard-container">

        <!-- Agency Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo" style="color: #60a5fa;">AGAP-Link</h1>
                <p class="user-panel-label">AGENCY PANEL</p>
            </div>

            <nav class="sidebar-nav">
                <span class="active-indicator"></span>

                <a href="<?= BASE_URL ?>/view/lgu_module/agency_dashboard.php" class="nav-item active">
                    <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span>
                    <span>Dashboard</span>
                </a>

            </nav>

            <div class="sidebar-footer">
                <a href="<?= BASE_URL ?>/view/auth/logout.php" class="nav-item logout">
                    <span class="nav-icon"><i data-lucide="log-out"></i></span>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <main class="main-content page-transition">

            <div class="agency-header">
                <h1>Welcome, <?= htmlspecialchars($agency_name) ?></h1>
                <p>View and manage reports forwarded to your agency. Verify reports to prevent false dispatches.</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-grid">
                <?php
                $totalReports   = count($agencyReports);
                $pendingCount   = count(array_filter($agencyReports, fn($r) => $r['status'] === 'Pending'));
                $ongoingCount   = count(array_filter($agencyReports, fn($r) => $r['status'] === 'Ongoing'));
                $resolvedCount  = count(array_filter($agencyReports, fn($r) => $r['status'] === 'Resolved'));
                ?>
                <div class="stat-card stat-card-blue">
                    <div class="stat-label">Total Reports</div>
                    <div class="stat-value"><?= $totalReports ?></div>
                    <div class="stat-sublabel">Forwarded to you</div>
                </div>
                <div class="stat-card stat-card-orange">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= $pendingCount ?></div>
                    <div class="stat-sublabel">Requires action</div>
                </div>
                <div class="stat-card stat-card-green">
                    <div class="stat-label">Ongoing</div>
                    <div class="stat-value"><?= $ongoingCount ?></div>
                    <div class="stat-sublabel">Being addressed</div>
                </div>
                <div class="stat-card stat-card-purple">
                    <div class="stat-label">Resolved</div>
                    <div class="stat-value"><?= $resolvedCount ?></div>
                    <div class="stat-sublabel">Completed</div>
                </div>
            </div>

            <!-- FILTER BAR -->
            <form method="GET" action="">
                <div class="filter-bar">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search"
                               placeholder="Description, address, reporter..."
                               value="<?= htmlspecialchars($filterSearch) ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                                    <?= $s ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter-apply">▼ Filter</button>
                    <a href="<?= BASE_URL ?>/view/lgu_module/agency_dashboard.php" class="btn-clear">Clear</a>
                </div>
            </form>

            <!-- REPORTS LIST -->
            <section class="reports-section">
                <?php if (!$hasReports): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <p class="empty-message">No reports found<?= ($filterStatus || $filterSearch) ? ' matching your filters.' : ' forwarded to your agency yet.' ?></p>
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
                                    <th>Verified</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agencyReports as $report): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($report['report_id']) ?></td>

                                    <td>
                                        <?= htmlspecialchars($report['category_name'] ?? 'General') ?>
                                        <span class="report-cell-sub">
                                            <?= htmlspecialchars(mb_strimwidth($report['description'] ?? '', 0, 60, '…')) ?>
                                        </span>
                                        <span class="report-cell-sub" style="color: #6b7280;">
                                            📍 <?= htmlspecialchars($report['address'] ?? 'No address') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($report['reporter_name'] ?? 'N/A') ?>
                                        <span class="report-cell-sub">
                                            📱 <?= htmlspecialchars($report['reporter_phone'] ?? 'N/A') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php
                                        $status = strtolower($report['status']);
                                        $statusClass = match ($status) {
                                            'pending'   => 'status-pending',
                                            'ongoing'   => 'status-ongoing',
                                            'verified'  => 'status-verified',
                                            'forwarded' => 'status-forwarded',
                                            'resolved'  => 'status-resolved',
                                            default     => 'status-pending'
                                        };
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                    </td>

                                    <td>
                                        <?php if ($report['is_verified']): ?>
                                            <span class="verify-badge verify-confirmed">✓ Verified</span>
                                        <?php else: ?>
                                            <span class="verify-badge verify-pending">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>

                                    <td class="action-cell">
                                        <div class="meatballs-container">
                                            <button type="button" class="meatballs-btn">⋮</button>
                                            <div class="meatballs-menu">
                                                <button type="button" class="view-details-btn"
                                                    data-id="<?= $report['report_id'] ?>"
                                                    data-category="<?= htmlspecialchars($report['category_name']) ?>"
                                                    data-description="<?= htmlspecialchars($report['description']) ?>"
                                                    data-reporter="<?= htmlspecialchars($report['reporter_name']) ?>"
                                                    data-phone="<?= htmlspecialchars($report['reporter_phone'] ?? '') ?>"
                                                    data-status="<?= htmlspecialchars($report['status']) ?>"
                                                    data-address="<?= htmlspecialchars($report['address'] ?? '') ?>"
                                                    data-date="<?= date('M d, Y', strtotime($report['created_at'])) ?>"
                                                    data-photo="<?= htmlspecialchars($report['photo_path'] ?? '') ?>">
                                                    View Details
                                                </button>

                                                <!-- VERIFY REPORT -->
                                                <?php if (empty($report['is_verified'])): ?>
                                                    <form method="POST"
                                                        action="<?= BASE_URL ?>/controller/agency_verify_report.php"
                                                        onsubmit="return confirm('Verify this report? This confirms the report is legitimate before updating status.');">
                                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                                        <button type="submit" style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:pointer; color:#1d4ed8; font-weight:600;">
                                                            ✓ Verify Report
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:default; color:#6b7280;" disabled>
                                                        ✓ Already Verified
                                                    </button>
                                                <?php endif; ?>

                                                <!-- Update Status with Remarks -->
                                                <button type="button" class="update-status-modal-btn" 
                                                    onclick="showUpdateModal(<?= $report['report_id'] ?>)"
                                                    style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:pointer; color:#059669; font-weight:600;">
                                                    Update Status with Remarks
                                                </button>
                                            </div>
                                        </div>
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

    <!-- UPDATE STATUS MODAL -->
    <div id="updateModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-modal" onclick="closeUpdateModal()">&times;</span>
            <h2>Update Report Status</h2>
            <form method="POST" action="<?= BASE_URL ?>/controller/agency_update_status.php">
                <input type="hidden" name="report_id" id="updateReportId">
                <div class="form-group">
                    <label for="updateStatus">New Status</label>
                    <select name="new_status" id="updateStatus" required>
                        <option value="">Select Status</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks (Optional)</label>
                    <textarea name="remarks" id="remarks" rows="4" 
                        placeholder="Add notes about the status update..."
                        style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; font-family:Arial, sans-serif;"></textarea>
                </div>
                <div style="text-align:right; margin-top:16px;">
                    <button type="submit" class="btn-primary">Update</button>
                    <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- REPORT DETAILS MODAL -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Report</h2>
            <div class="modal-body">
                <div id="modalPhotoWrapper" style="margin-bottom:16px; display:none;">
                    <strong>Photo Evidence:</strong><br>
                    <img id="modalPhoto" src="" alt="Report photo" style="max-width:100%; max-height:300px; margin-top:8px; border-radius:8px; object-fit:contain;">
                </div>
                <p><strong>Category:</strong> <span id="modalCategory"></span></p>
                <p><strong>Description:</strong></p>
                <p id="modalDescription"></p>
                <p><strong>Location:</strong> <span id="modalAddress"></span></p>
                <p><strong>Reporter:</strong> <span id="modalReporter"></span></p>
                <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Date:</strong> <span id="modalDate"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeModalBtn" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        document.addEventListener("DOMContentLoaded", () => {

            // Mobile menu toggle
            const menuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.querySelector('.sidebar');
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('sidebar-open');
                });
            }

            // Meatballs menu
            document.querySelectorAll(".meatballs-btn").forEach(btn => {
                btn.addEventListener("click", e => {
                    e.stopPropagation();
                    document.querySelectorAll(".meatballs-menu").forEach(m => m.style.display = "none");
                    btn.nextElementSibling.style.display = "block";
                });
            });
            document.addEventListener("click", () => {
                document.querySelectorAll(".meatballs-menu").forEach(m => m.style.display = "none");
            });

            // Modal
            const modal = document.getElementById("reportModal");
            const closeModal = document.querySelector(".close-modal");
            const closeModalBtn = document.getElementById("closeModalBtn");

            document.querySelectorAll(".view-details-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.getElementById("modalTitle").textContent = "Report #" + btn.dataset.id;
                    document.getElementById("modalCategory").textContent = btn.dataset.category;
                    document.getElementById("modalDescription").textContent = btn.dataset.description;
                    document.getElementById("modalAddress").textContent = btn.dataset.address || "N/A";
                    document.getElementById("modalReporter").textContent = btn.dataset.reporter;
                    document.getElementById("modalPhone").textContent = btn.dataset.phone || "N/A";
                    document.getElementById("modalStatus").textContent = btn.dataset.status;
                    document.getElementById("modalDate").textContent = btn.dataset.date;

                    const photoWrapper = document.getElementById("modalPhotoWrapper");
                    const photoEl = document.getElementById("modalPhoto");
                    if (btn.dataset.photo && btn.dataset.photo !== '') {
                        photoEl.src = btn.dataset.photo;
                        photoWrapper.style.display = "block";
                    } else {
                        photoWrapper.style.display = "none";
                        photoEl.src = '';
                    }

                    modal.style.display = "flex";
                });
            });

            const closeM = () => { modal.style.display = "none"; };
            closeModal.addEventListener("click", closeM);
            closeModalBtn.addEventListener("click", closeM);
            modal.addEventListener("click", e => { if (e.target === modal) closeM(); });

            // Update Status Modal Functions
            const updateModal = document.getElementById("updateModal");
            if (updateModal) {
                updateModal.addEventListener("click", function(e) {
                    if (e.target === this) closeUpdateModal();
                });
            }
        });

        window.showUpdateModal = function(reportId) {
            document.getElementById("updateReportId").value = reportId;
            document.getElementById("updateModal").style.display = "flex";
        };

        window.closeUpdateModal = function() {
            document.getElementById("updateModal").style.display = "none";
        };
    </script>
</body>

</html>

