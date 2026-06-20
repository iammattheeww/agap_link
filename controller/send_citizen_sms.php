<?php
require_once dirname(__DIR__) . "/config/init.php";

header('Content-Type: application/json'); // Crucial for JS fetch to parse correctly

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

require_once MODEL_PATH . 'SmsNotifier.php';
require_once MODEL_PATH . 'Report.php';

$report_id = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';


if (!$report_id || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing report_id or action']);
    exit();
}

try {
    if ($action === 'send_status_sms') {
        $reportModel = new Report();
        $report = $reportModel->getReportById($report_id);

        if (!$report) {
            throw new Exception("Report #$report_id not found");
        }

        SmsNotifier::sendStatusUpdate($report_id, $report['status']);

        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("SMS Error Report #$report_id: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
exit();
