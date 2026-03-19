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

    // ✅ Get current report BEFORE update (important)
    $report = $reportModel->getReportById($report_id);

    // ✅ Update status
    $reportModel->updateReportStatus($report_id, $new_status, $remarks ?: null);

    // ✅ Send SMS ONLY if status changed
    if ($report && $report['status'] !== $new_status) {
        try {
            require_once MODEL_PATH . 'SmsNotifier.php';
            SmsNotifier::sendStatusUpdate($report_id, $new_status);
        } catch (Exception $e) {
            error_log("SMS failed for report #$report_id: " . $e->getMessage());
        }
    }

    $_SESSION['success'] = "Report #$report_id updated to $new_status.";

} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error. Try again.';
}

header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
exit();