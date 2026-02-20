<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'SmsNotifier.php';
require_once MODEL_PATH . 'Report.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$report_id     = isset($_POST['report_id'])      ? (int)$_POST['report_id']          : 0;
$customMessage = isset($_POST['custom_message']) ? trim($_POST['custom_message'])      : '';

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID.']);
    exit();
}

try {
    $reportModel = new Report();
    $report      = $reportModel->getReportById($report_id);

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found.']);
        exit();
    }

    if (empty($customMessage)) {
        // Use default status-based message from SmsNotifier
        SmsNotifier::sendStatusUpdate($report_id, $report['status']);
    } else {
        // Send custom message directly
        SmsNotifier::sendCustomSms($report['reporter_phone'], $customMessage);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
