<?php
require_once dirname(__DIR__) . "/config/init.php";

if (!isset($_SESSION['agency_logged_in']) || $_SESSION['agency_logged_in'] !== true) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
    exit();
}

require_once MODEL_PATH . 'Report.php'; // FIX: was missing .php extension

$report_id  = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);
$new_status = $_POST['new_status'] ?? '';

$allowed_statuses = ['Ongoing', 'Resolved'];

if (!$report_id || !in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
    exit();
}

try {
    $reportModel = new Report();
    $reportModel->updateReportStatus(
        $report_id,
        $new_status,
        "Updated by agency: " . $_SESSION['agency_name']
    );

    // Send SMS notification to the citizen
    try {
        require_once MODEL_PATH . 'SmsNotifier.php';
        SmsNotifier::sendStatusUpdate($report_id, $new_status);
    } catch (Exception $e) {
        // Log but don't block the redirect — SMS failure is non-fatal
        error_log("SMS notification failed for report #$report_id: " . $e->getMessage());
    }

    $_SESSION['success'] = "Report #$report_id status updated to $new_status.";

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to update status. Please try again.';
}

header("Location: " . BASE_URL . "/view/lgu_module/agency_dashboard.php");
exit();

