<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'Report.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/view/user_module/my_reports.php');
    exit();
}

$report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;

if ($report_id <= 0) {
    $_SESSION['error'] = 'Invalid report ID.';
    header('Location: ' . BASE_URL . '/view/user_module/my_reports.php');
    exit();
}

try {
    $reportModel = new Report();

    // Fetch the report to delete any associated photo file
    $report = $reportModel->getReportById($report_id);

    // Security: ensure this report belongs to the logged-in user
    if (!$report || (int)$report['user_id'] !== (int)$_SESSION['user_id']) {
        $_SESSION['error'] = 'You are not authorized to delete this report.';
        header('Location: ' . BASE_URL . '/view/user_module/my_reports.php');
        exit();
    }

    // Delete associated photo from filesystem if it exists
    if (!empty($report['photo_path'])) {
        $fullPath = dirname(__DIR__) . $report['photo_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // Delete report from database (cascades to report_logs)
    $result = $reportModel->deleteUserReport($report_id, (int)$_SESSION['user_id']);

    if ($result) {
        $_SESSION['success'] = 'Report #' . str_pad($report_id, 4, '0', STR_PAD_LEFT) . ' has been deleted successfully.';
    } else {
        $_SESSION['error'] = 'Could not delete report. Please try again.';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Delete failed: ' . $e->getMessage();
}

header('Location: ' . BASE_URL . '/view/user_module/my_reports.php');
exit();
