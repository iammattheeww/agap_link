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

// ─── FILTER PARAMETERS ──────────────────────────────────────────────────
$filterStatus   = $_GET['status']   ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterSearch   = trim($_GET['search'] ?? '');

// ─── USE MODEL FOR ALL QUERIES ───────────────────────────────────────────
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
    <style>
        .filter-bar {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }
        .filter-bar .form-group { margin: 0; flex: 1 1 160px; }
        .filter-bar label { font-size: 0.8rem; font-weight: 600; color: var(--color-gray-600); display: block; margin-bottom: 6px; }
        .filter-bar select,
        .filter-bar input[type="text"] {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-family: var(--font-primary);
            font-size: 0.9rem;
            background: var(--color-white);
            transition: border-color var(--transition-fast);
        }
        .filter-bar select:focus,
        .filter-bar input[type="text"]:focus { border-color: var(--color-primary); outline: none; }
        .filter-bar .btn-filter-apply {
            padding: 10px 20px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-family: var(--font-primary);
            cursor: pointer;
            transition: background var(--transition-fast);
            align-self: flex-end;
        }
        .filter-bar .btn-filter-apply:hover { background: var(--color-primary-dark); }
        .filter-bar .btn-clear {
            padding: 10px 16px;
            background: var(--color-gray-100);
            color: var(--color-gray-800);
            border: 1.5px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-family: var(--font-primary);
            font-weight: 600;
            cursor: pointer;
            align-self: flex-end;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
        }
        .filter-bar .btn-clear:hover { background: var(--color-gray-200); }

        /* Active filter indicator */
        .active-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .filter-tag {
            background: rgba(249,115,22,0.1);
            color: var(--color-primary-dark);
            border: 1px solid rgba(249,115,22,0.3);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Inline status update form in table */
        .status-update-form { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .status-update-form select { padding: 5px 10px; font-size: 0.85rem; min-width: 110px; }
        .btn-update-status {
            padding: 5px 12px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-primary);
        }
        .btn-update-status:hover { background: var(--color-primary-dark); }
    </style>
</head>

<body>
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
                                    <th>Update Status</th>
                                    <th>Forwarded To</th>
                                    <th>Date</th>
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
