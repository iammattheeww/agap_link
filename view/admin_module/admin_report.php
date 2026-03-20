<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once dirname(__DIR__, 2) . "/config/agaplinkdb.php";
require_once MODEL_PATH . 'Report.php';

// FILTER PARAMETERS
$filterStatus   = $_GET['status']   ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterSearch   = trim($_GET['search'] ?? '');

// USE MODEL FOR ALL QUERIES
$reportModel = new Report();
$allReports  = $reportModel->getFilteredReports($filterStatus, $filterCategory, $filterSearch);
$hasReports  = !empty($allReports);
$categories  = $reportModel->getAllCategories();
$agencies    = $reportModel->getAllAgencies();

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
    <title>Reports Management - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_module.css">
</head>

<body data-base-url="<?= BASE_URL ?>">
    <?php require VIEW_PATH . 'partials/mobile_topnav_admin.php'; ?>

    <div class="dashboard-container">

        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <main class="main-content page-transition">

            <div class="reports-header">
                <div>
                    <h1>Reports Management</h1>
                    <p>Review, manage, and update community reports.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

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
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"
                                    <?= $filterCategory == $cat['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter-apply">&#x25BC; Filter</button>
                    <a href="<?= BASE_URL ?>/view/admin_module/admin_report.php" class="btn-clear">Clear</a>
                </div>
            </form>

            <!-- ACTIVE FILTER TAGS -->
            <?php if ($filterStatus || $filterCategory || $filterSearch): ?>
                <div class="active-filters">
                    <?php if ($filterStatus): ?>
                        <span class="filter-tag">Status: <?= htmlspecialchars($filterStatus) ?></span>
                    <?php endif; ?>
                    <?php if ($filterCategory): ?>
                        <?php $catName = array_column($categories, 'name', 'category_id')[$filterCategory] ?? $filterCategory; ?>
                        <span class="filter-tag">Category: <?= htmlspecialchars($catName) ?></span>
                    <?php endif; ?>
                    <?php if ($filterSearch): ?>
                        <span class="filter-tag">Search: "<?= htmlspecialchars($filterSearch) ?>"</span>
                    <?php endif; ?>
                    <span class="filter-tag" style="background:var(--color-gray-100); color:var(--color-gray-600); border-color:var(--color-gray-300);">
                        <?= count($allReports) ?> result<?= count($allReports) !== 1 ? 's' : '' ?>
                    </span>
                </div>
            <?php endif; ?>

            <section class="reports-section">
                <?php if (!$hasReports): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <p class="empty-message">No reports found<?= ($filterStatus || $filterCategory || $filterSearch) ? ' matching your filters.' : ' yet.' ?></p>
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
                                    <th>Update Status</th>
                                    <th>Forwarded To</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allReports as $report): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($report['report_id']) ?></td>

                                        <td>
                                            <?= htmlspecialchars($report['category_name'] ?? 'General') ?>
                                            <span class="report-cell-sub">
                                                <?= htmlspecialchars(mb_strimwidth($report['description'] ?? '', 0, 70, '…')) ?>
                                            </span>
                                        </td>

                                        <td><?= htmlspecialchars($report['full_name'] ?? 'N/A') ?></td>

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
                                            <?php if (!empty($report['is_verified'])): ?>
                                                <span style="color: #059669; font-weight: 600;">✓ Verified</span>
                                            <?php else: ?>
                                                <span style="color: #92400e; font-weight: 600;">Pending</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <form method="POST"
                                                action="<?= BASE_URL ?>/controller/update_report_status.php"
                                                class="status-update-form">
                                                <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                                <select name="new_status">
                                                    <?php foreach ($statuses as $s): ?>
                                                        <option value="<?= $s ?>"
                                                            <?= $report['status'] === $s ? 'selected' : '' ?>>
                                                            <?= $s ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-update-status">Update</button>
                                            </form>
                                        </td>

                                        <td><?= htmlspecialchars($report['agency_name'] ?? '—') ?></td>
                                        <td><?= date('M d, Y', strtotime($report['created_at'])) ?></td>

                                        <!-- ACTIONS COLUMN -->
                                        <td class="action-cell">
                                            <div class="meatballs-container">

                                                <button type="button" class="meatballs-btn">⋮</button>

                                                <div class="meatballs-menu">

                                                    <!-- VIEW DETAILS -->
                                                    <button type="button"
                                                        class="view-details-btn"
                                                        data-id="<?= $report['report_id'] ?>"
                                                        data-category="<?= htmlspecialchars($report['category_name']) ?>"
                                                        data-description="<?= htmlspecialchars($report['description']) ?>"
                                                        data-reporter="<?= htmlspecialchars($report['full_name']) ?>"
                                                        data-phone="<?= htmlspecialchars($report['reporter_phone'] ?? '') ?>"
                                                        data-status="<?= htmlspecialchars($report['status']) ?>"
                                                        data-agency="<?= htmlspecialchars($report['agency_name'] ?? '—') ?>"
                                                        data-date="<?= date('M d, Y', strtotime($report['created_at'])) ?>"
                                                        data-photo="<?= htmlspecialchars($report['photo_path'] ?? '') ?>">
                                                        View Details
                                                    </button>

                                                    <!-- VERIFY (prevents prank dispatches) -->
                                                    <?php if (empty($report['is_verified'])): ?>
                                                        <form method="POST"
                                                            action="<?= BASE_URL ?>/controller/verify_report.php"
                                                            onsubmit="return confirm('Verify this report? This confirms the report is legitimate before forwarding to agencies.');">
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

                                                    <!-- FORWARD TO AGENCY -->
                                                    <?php if (!empty($report['is_verified']) && empty($report['assigned_agency_id'])): ?>
                                                        <button type="button" class="forward-btn" onclick="showForwardModal(<?= $report['report_id'] ?>)" style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:pointer; color:#7c3aed; font-weight:600;">
                                                            → Forward to Agency
                                                        </button>
                                                    <?php elseif (!empty($report['assigned_agency_id'])): ?>
                                                        <button type="button" style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:default; color:#6b7280;" disabled>
                                                            → Already Forwarded
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" style="width:100%; padding:10px; border:none; background:none; text-align:left; cursor:not-allowed; color:#9ca3af;" disabled>
                                                            → Verify First
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- ARCHIVE (soft-hide, never permanently deleted) -->
                                                    <form method="POST"
                                                        action="<?= BASE_URL ?>/controller/archive_report.php"
                                                        onsubmit="return confirm('Archive this report? It will be hidden from the active list but permanently preserved.');">
                                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                                        <button type="submit" class="archive-btn">Archive</button>
                                                    </form>

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

    <!-- REPORT DETAILS MODAL -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Report</h2>
            <div class="modal-body">
                <!-- Photo display -->
                <div id="modalPhotoWrapper" style="margin-bottom:16px; display:none;">
                    <strong>Photo Evidence:</strong><br>
                    <img id="modalPhoto" src="" alt="Report photo"
                        style="max-width:100%; max-height:300px; margin-top:8px; border-radius:8px; object-fit:contain;">
                </div>
                <p><strong>Category:</strong> <span id="modalCategory"></span></p>
                <p><strong>Description:</strong></p>
                <p id="modalDescription"></p>
                <p><strong>Reporter:</strong> <span id="modalReporter"></span></p>
                <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Forwarded To:</strong> <span id="modalAgency"></span></p>
                <p><strong>Date:</strong> <span id="modalDate"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" id="messageCitizenBtn" class="btn-primary">
                    Message Citizen via SMS
                </button>
            </div>
        </div>
    </div>

    <!-- FORWARD TO AGENCY MODAL -->
    <div id="forwardModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <span class="close-modal" id="closeForwardModal">&times;</span>
            <h2>Forward Report to Agency</h2>
            <div class="modal-body">
                <p style="margin-bottom: 16px;">Select an agency to forward this report to:</p>
                <form method="POST" action="<?= BASE_URL ?>/controller/forward_report.php" id="forwardForm">
                    <input type="hidden" name="report_id" id="forwardReportId">
                    <div class="form-group">
                        <label class="form-label" for="agency_id">Select Agency</label>
                        <select name="agency_id" id="agencySelect" class="form-input" required>
                            <option value="">-- Select Agency --</option>
                            <?php foreach ($agencies as $agency): ?>
                                <option value="<?= $agency['agency_id'] ?>"><?= htmlspecialchars($agency['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn-primary">Forward Report</button>
                        <button type="button" class="btn-secondary" id="cancelForwardModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <script src="<?= ASSET_URL ?>/js/admin_module/admin_reports.js"></script>

</body>

</html>