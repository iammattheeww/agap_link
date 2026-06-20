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
require_once MODEL_PATH . 'SmsNotifier.php'; // 1. Require the Notifier

$report_id  = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);
$new_status = $_POST['new_status'] ?? '';
$remarks    = trim($_POST['remarks'] ?? '');

$allowed_statuses = ['Pending', 'Verified', 'Forwarded', 'Ongoing', 'Resolved'];

if (!$report_id || !in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
    exit();
}

try {
    $reportModel = new Report();

    // UPDATE STATUS
    $reportModel->updateReportStatus($report_id, $new_status, $remarks ?: null);

    // 2. SEND SMS AUTOMATICALLY ON STATUS UPDATE
    try {
        SmsNotifier::sendStatusUpdate($report_id, $new_status);
        $_SESSION['success'] = "Report #$report_id updated to $new_status. SMS Sent!";
    } catch (Exception $e) {
        // If SMS fails, log it, but still acknowledge the status update success
        error_log("SMS Error Report #$report_id: " . $e->getMessage());
        $_SESSION['success'] = "Report #$report_id updated to $new_status, but SMS notification failed.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error. Try again.';
}

header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
exit();
