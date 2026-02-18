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

require_once dirname(__DIR__) . "/config/agaplinkdb.php";

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
    $conn->beginTransaction();

    // Update report status
    $updateStmt = $conn->prepare("UPDATE reports SET status = ?, updated_at = NOW() WHERE report_id = ?");
    $updateStmt->execute([$new_status, $report_id]);

    // Insert into report_logs
    $logStmt = $conn->prepare(
        "INSERT INTO report_logs (report_id, status_change, remarks, timestamp) VALUES (?, ?, ?, NOW())"
    );
    $logStmt->execute([$report_id, $new_status, $remarks ?: null]);

    $conn->commit();

    // Attempt email notification (ADD-2) — failure won't block status update
    try {
        require_once dirname(__DIR__) . "/model/Mailer.php";
        sendStatusUpdateEmail($conn, $report_id, $new_status, $remarks);
    } catch (Exception $e) {
        // Log email failure to report_logs
        try {
            $errStmt = $conn->prepare(
                "INSERT INTO report_logs (report_id, status_change, remarks, timestamp) VALUES (?, ?, ?, NOW())"
            );
            $errStmt->execute([$report_id, 'EMAIL_FAILED', 'Email error: ' . $e->getMessage()]);
        } catch (Exception $inner) {
            // silent
        }
    }

    $_SESSION['success'] = "Report #$report_id status updated to $new_status.";

} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = 'Failed to update status. Please try again.';
}

header("Location: " . BASE_URL . "/view/admin_module/admin_report.php");
exit();
