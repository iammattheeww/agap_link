<?php
require_once dirname(__DIR__) . "/config/init.php";

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

require_once MODEL_PATH . 'Report.php';
require_once MODEL_PATH . 'SmsNotifier.php';

$report_id = filter_var($_POST['report_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$report_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing report_id']);
    exit();
}

try {
    $reportModel = new Report();
    $report = $reportModel->getReportById($report_id);
    
    if (!$report) {
        throw new Exception("Report #$report_id not found");
    }
    
    if (!$report['assigned_agency_id']) {
        throw new Exception("Report not yet forwarded to agency");
    }
    
    $agency_name = $report['agency_name'] ?? 'LGU';
    $status = trim($report['status']);
    $phone = $report['reporter_phone'];
    
    // Debug: log the actual status value
    error_log("ADMIN SMS DEBUG - ReportID: $report_id | Phone: $phone | Raw Status: '" . $report['status'] . "' | Trimmed Status: '$status' | is_verified: " . ($report['is_verified'] ?? 0));
    
    // Match actual database status values: Pending, Forwarded, Ongoing, Resolved
    $message = match ($status) {
        'Pending' => "Report update: Your concern has been received by $agency_name and is under review.",
        'Forwarded' => "Report update: Your concern has been assigned to $agency_name who will take action.",
        'Ongoing' => "Report update: $agency_name is now working on your concern.",
        'Resolved' => "Report update: $agency_name has completed work on your concern. Thank you.",
        default => "Report update: Your concern status: $status. $agency_name has it."
    };
    
    error_log("ADMIN SMS - Selected message for status '$status': " . mb_substr($message, 0, 100));
    
    // Send SMS to citizen
    SmsNotifier::sendRawSMS($phone, $message);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'SMS sent to citizen']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    error_log("Admin SMS Error Report #$report_id: " . $e->getMessage());
}
?>
