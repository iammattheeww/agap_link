<?php
require_once dirname(__DIR__) . "/config/init.php";

// Only agencies can verify reports
if (!isset($_SESSION['agency_logged_in']) || $_SESSION['agency_logged_in'] !== true) {
    http_response_code(403);
    $_SESSION['error'] = 'Unauthorized. Only agencies can verify reports.';
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';
require_once MODEL_PATH . 'SmsNotifier.php';

$report_id = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);
$agency_id = $_SESSION['agency_id'];

if (!$report_id) {
    $_SESSION['error'] = 'Invalid report ID.';
    header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
    exit();
}

try {
    $reportModel = new Report();
    
    // Verify the report belongs to this agency
    $report = $reportModel->getReportById($report_id);
    if (!$report || $report['assigned_agency_id'] != $agency_id) {
        $_SESSION['error'] = 'You can only verify reports assigned to your agency.';
        header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
        exit();
    }
    
    // Verify the report - NO SMS (admin will handle citizen communication)
    if ($reportModel->verifyReport($report_id)) {
        $_SESSION['success'] = "Report #$report_id has been verified. Admin will be notified.";
    } else {
        $_SESSION['error'] = 'Failed to verify report. Please try again.';
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
exit();
?>
