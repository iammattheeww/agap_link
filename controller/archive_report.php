<?php
require_once dirname(__DIR__) . "/config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

$report_id = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$report_id) {
    $_SESSION['error'] = 'Invalid report ID.';
    header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
    exit();
}

try {
    $reportModel = new Report();
    $reportModel->archiveReport($report_id);
    $_SESSION['success'] = "Report #$report_id has been archived.";
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to archive report. Please try again.';
}

header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
exit();
