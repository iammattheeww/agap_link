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
$agency_id = filter_var($_POST['agency_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$report_id || !$agency_id) {
    $_SESSION['error'] = 'Invalid report or agency.';
    header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
    exit();
}

try {
    $reportModel = new Report();

    // Forward using model's conn (not raw $conn which is out of scope inside try)
    $reportModel->forwardReport($report_id, $agency_id);

    // Send SMS notification to the reporter
    try {
        require_once MODEL_PATH . 'SmsNotifier.php';
        SmsNotifier::sendStatusUpdate($report_id, 'Forwarded');
    } catch (Exception $e) {
        error_log("SMS notification failed for report #$report_id: " . $e->getMessage());
    }

    $_SESSION['success'] = "Report #$report_id has been forwarded to the agency.";

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to forward report. Please try again.';
}

header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
exit();
