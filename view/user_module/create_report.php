<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

// FETCH CATEGORIES USING MODEL (MVC Compliant)
$reportModel = new Report();
$categories = $reportModel->getAllCategories();

// GET USER NAME FROM SESSION
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>Create Report - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once VIEW_PATH . 'partials/user_sidebar.php'; ?>

        <main class="main-content">
            <div class="create-report-container">
                <div class="page-header">
                    <h1 class="page-title">Create New Report</h1>
                    <p class="page-description">
                        Help improve your community by reporting issues. Provide as much detail as possible to help us address the problem quickly.
                    </p>
                </div>
                <?php if (isset($_SESSION['error'])): ?>    
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Report submitted successfully!
                    </div>
                <?php endif; ?>               
            </div>
        </main>
    </div>
    <script src="<?= ASSET_URL ?>/js/user_module/create_report.js"></script>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

</html>