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
     <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_reports.css">
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
                <div class="reports-header-actions">
                    <button type="button" id="exportReportsBtn" class="btn-export">
                        Export to Excel
                    </button>

                    <a href="<?= BASE_URL ?>/view/admin_module/export_reports_pdf.php?status=<?= urlencode($filterStatus) ?>&category=<?= urlencode($filterCategory) ?>&search=<?= urlencode($filterSearch) ?>" 
   class="btn-export-pdf"
   target="_blank">
   Export to PDF
</a>
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
                        <table class="reports-table" id="reportsTable">
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
                                    <tr
                                        data-report-id="<?= htmlspecialchars($report['report_id']) ?>"
                                        data-category="<?= htmlspecialchars($report['category_name'] ?? 'General') ?>"
                                        data-description="<?= htmlspecialchars($report['description'] ?? '') ?>"
                                        data-reporter="<?= htmlspecialchars($report['full_name'] ?? 'N/A') ?>"
                                        data-status="<?= htmlspecialchars($report['status']) ?>"
                                        data-verified="<?= !empty($report['is_verified']) ? 'Verified' : 'Pending' ?>"
                                        data-agency="<?= htmlspecialchars($report['agency_name'] ?? '—') ?>"
                                        data-date="<?= date('M d, Y', strtotime($report['created_at'])) ?>">

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

    <!-- EXPORT PROGRESS TOAST -->
    <div id="exportToast" class="export-toast" style="display:none;">
        <span id="exportToastMsg">Preparing export…</span>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>

    <!-- SheetJS CDN for Excel export -->
     <!-- SheetJS is a library! -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> 

    <!-- main.js para mag gana ang hamburger icon kung mag mobile view -->
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>

    <!-- admin_report.js -->
    <script src="<?= ASSET_URL ?>/js/admin_module/admin_reports.js"></script>

</body>
<!-- <script>
    /* ============================================================
   admin_reports.js  –  AGAP-Link Admin Reports Module
   Handles: meatball menus, view-details modal, forward modal,
            status-update form, and Excel export.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ── MEATBALLS MENU ──────────────────────────────────────────
    document.querySelectorAll('.meatballs-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close all other open menus first
            document.querySelectorAll('.meatballs-menu.open').forEach(m => {
                if (m !== this.nextElementSibling) m.classList.remove('open');
            });
            this.nextElementSibling.classList.toggle('open');
        });
    });

    // Close meatballs when clicking outside
    document.addEventListener('click', function () {
        document.querySelectorAll('.meatballs-menu.open').forEach(m => m.classList.remove('open'));
    });


    // ── VIEW DETAILS MODAL ──────────────────────────────────────
    const reportModal    = document.getElementById('reportModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');

    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').textContent    = 'Report #' + this.dataset.id;
            document.getElementById('modalCategory').textContent = this.dataset.category  || '—';
            document.getElementById('modalDescription').textContent = this.dataset.description || '—';
            document.getElementById('modalReporter').textContent = this.dataset.reporter  || '—';
            document.getElementById('modalPhone').textContent    = this.dataset.phone     || '—';
            document.getElementById('modalStatus').textContent   = this.dataset.status    || '—';
            document.getElementById('modalAgency').textContent   = this.dataset.agency    || '—';
            document.getElementById('modalDate').textContent     = this.dataset.date      || '—';

            // Photo
            const photoWrapper = document.getElementById('modalPhotoWrapper');
            const photoEl      = document.getElementById('modalPhoto');
            if (this.dataset.photo) {
                photoEl.src          = this.dataset.photo;
                photoWrapper.style.display = 'block';
            } else {
                photoWrapper.style.display = 'none';
            }

            // Store reporter info on the SMS button
            const smsBtn = document.getElementById('messageCitizenBtn');
            if (smsBtn) {
                smsBtn.dataset.phone    = this.dataset.phone    || '';
                smsBtn.dataset.reporter = this.dataset.reporter || '';
                smsBtn.dataset.id       = this.dataset.id       || '';
            }

            reportModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Close meatball menu
            document.querySelectorAll('.meatballs-menu.open').forEach(m => m.classList.remove('open'));
        });
    });

    // Close modals
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    window.addEventListener('click', function (e) {
        if (e.target === reportModal) closeAllModals();
        const forwardModal = document.getElementById('forwardModal');
        if (e.target === forwardModal) closeAllModals();
    });

    function closeAllModals() {
        const reportModal  = document.getElementById('reportModal');
        const forwardModal = document.getElementById('forwardModal');
        if (reportModal)  reportModal.style.display  = 'none';
        if (forwardModal) forwardModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Cancel button on forward modal
    const cancelForward = document.getElementById('cancelForwardModal');
    if (cancelForward) {
        cancelForward.addEventListener('click', closeAllModals);
    }


    // ── FORWARD TO AGENCY MODAL ─────────────────────────────────
    window.showForwardModal = function (reportId) {
        const modal = document.getElementById('forwardModal');
        document.getElementById('forwardReportId').value = reportId;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.querySelectorAll('.meatballs-menu.open').forEach(m => m.classList.remove('open'));
    };


    // ── SMS BUTTON ──────────────────────────────────────────────
    const smsBtn = document.getElementById('messageCitizenBtn');
    if (smsBtn) {
        smsBtn.addEventListener('click', function () {
            const phone = this.dataset.phone;
            if (!phone) {
                alert('No phone number on record for this reporter.');
                return;
            }
            window.open('sms:' + phone, '_blank');
        });
    }


    // ── AUTO-DISMISS ALERTS ─────────────────────────────────────
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });


    // ── EXPORT TO EXCEL ─────────────────────────────────────────
    const exportBtn = document.getElementById('exportReportsBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportReportsToExcel);
    }

    function exportReportsToExcel() {
        // Show toast
        showExportToast('Preparing export…');

        // Small timeout so the toast renders before heavy work
        setTimeout(() => {
            try {
                const rows = document.querySelectorAll('#reportsTable tbody tr');

                if (!rows.length) {
                    showExportToast('No reports to export.', true);
                    return;
                }

                // ── Build data array from data-* attributes on each <tr> ──
                // Using data attributes avoids reading HTML entities / badge markup
                const headers = [
                    'Report ID',
                    'Category',
                    'Description',
                    'Reporter',
                    'Status',
                    'Verified',
                    'Forwarded To',
                    'Date Submitted'
                ];

                const data = [headers];

                rows.forEach(row => {
                    data.push([
                        row.dataset.reportId   || '',
                        row.dataset.category   || '',
                        row.dataset.description || '',
                        row.dataset.reporter   || '',
                        row.dataset.status     || '',
                        row.dataset.verified   || '',
                        row.dataset.agency     || '',
                        row.dataset.date       || ''
                    ]);
                });

                // ── Create workbook ──────────────────────────────────────
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(data);

                // Column widths (characters)
                ws['!cols'] = [
                    { wch: 10 },  // Report ID
                    { wch: 22 },  // Category
                    { wch: 55 },  // Description
                    { wch: 24 },  // Reporter
                    { wch: 13 },  // Status
                    { wch: 13 },  // Verified
                    { wch: 28 },  // Forwarded To
                    { wch: 16 },  // Date Submitted
                ];

                // Freeze the header row
                ws['!freeze'] = { xSplit: 0, ySplit: 1 };

                // Style header row: bold + green background
                const range = XLSX.utils.decode_range(ws['!ref']);
                for (let col = range.s.c; col <= range.e.c; col++) {
                    const cellAddr = XLSX.utils.encode_cell({ r: 0, c: col });
                    if (!ws[cellAddr]) continue;
                    ws[cellAddr].s = {
                        font:      { bold: true, color: { rgb: 'FFFFFF' }, sz: 11 },
                        fill:      { fgColor: { rgb: '166534' } },  // dark green
                        alignment: { horizontal: 'center', vertical: 'center', wrapText: false },
                        border: {
                            bottom: { style: 'thin', color: { rgb: 'BBBBBB' } }
                        }
                    };
                }

                // Zebra rows: light green on even data rows
                for (let row = 1; row <= range.e.r; row++) {
                    for (let col = range.s.c; col <= range.e.c; col++) {
                        const cellAddr = XLSX.utils.encode_cell({ r: row, c: col });
                        if (!ws[cellAddr]) {
                            // Create empty cell so we can style it
                            ws[cellAddr] = { t: 's', v: '' };
                        }
                        ws[cellAddr].s = {
                            fill:      { fgColor: { rgb: row % 2 === 0 ? 'F0FDF4' : 'FFFFFF' } },
                            font:      { sz: 10 },
                            alignment: { vertical: 'center', wrapText: col === 2 }  // wrap Description col
                        };
                    }
                }

                XLSX.utils.book_append_sheet(wb, ws, 'Reports');

                // ── Add a Summary sheet ──────────────────────────────────
                const totalRows    = rows.length;
                const statusCounts = {};
                rows.forEach(row => {
                    const s = (row.dataset.status || 'Unknown').trim();
                    statusCounts[s] = (statusCounts[s] || 0) + 1;
                });

                const summaryData = [
                    ['AGAP-Link Reports Summary'],
                    [],
                    ['Export Date', new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })],
                    ['Total Reports Exported', totalRows],
                    [],
                    ['Status Breakdown', ''],
                    ['Status', 'Count'],
                    ...Object.entries(statusCounts).map(([s, c]) => [s, c])
                ];

                const ws2 = XLSX.utils.aoa_to_sheet(summaryData);
                ws2['!cols'] = [{ wch: 30 }, { wch: 20 }];

                // Style the title cell
                if (ws2['A1']) {
                    ws2['A1'].s = {
                        font: { bold: true, sz: 14, color: { rgb: '166534' } }
                    };
                }

                XLSX.utils.book_append_sheet(wb, ws2, 'Summary');

                // ── Generate filename with date ──────────────────────────
                const today    = new Date();
                const dateStr  = today.toISOString().slice(0, 10);  // YYYY-MM-DD
                const filename = `agaplink_reports_${dateStr}.xlsx`;

                // Write the file (triggers browser download)
                XLSX.writeFile(wb, filename, { bookType: 'xlsx', type: 'binary', cellStyles: true });

                showExportToast(`✓ Exported ${totalRows} report${totalRows !== 1 ? 's' : ''} successfully!`, false, true);

            } catch (err) {
                console.error('Export error:', err);
                showExportToast('Export failed. Please try again.', true);
            }
        }, 50);
    }

    // Toast helper
    function showExportToast(message, isError = false, isSuccess = false) {
        const toast   = document.getElementById('exportToast');
        const msgEl   = document.getElementById('exportToastMsg');
        if (!toast || !msgEl) return;

        msgEl.textContent     = message;
        toast.style.display   = 'flex';
        toast.className       = 'export-toast';

        if (isError)   toast.classList.add('export-toast--error');
        if (isSuccess) toast.classList.add('export-toast--success');

        // Auto-hide after 3s
        clearTimeout(toast._hideTimer);
        toast._hideTimer = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.style.display  = 'none';
                toast.style.opacity  = '';
                toast.className      = 'export-toast';
            }, 400);
        }, 3000);
    }

}); // end DOMContentLoaded
</script> -->
</html>