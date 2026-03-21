<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'SmsNotifier.php';
require_once MODEL_PATH . 'Report.php';

header('Content-Type: application/json');

// ── AUTH CHECK ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Unauthorized. You must be logged in as an admin.',
    ]);
    exit();
}

// ── METHOD CHECK ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid request method. Only POST is accepted.',
    ]);
    exit();
}

// ── READ & DECODE JSON BODY ───────────────────────────────────────────────────
$rawInput = file_get_contents('php://input');
$data     = json_decode($rawInput, true);

if (!is_array($data) || empty($data)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid or empty JSON body.',
    ]);
    exit();
}

// ── EXTRACT & VALIDATE FIELDS ─────────────────────────────────────────────────
$report_id = isset($data['report_id']) ? (int) $data['report_id'] : 0;
$status    = isset($data['status'])    ? trim($data['status'])    : '';

if ($report_id <= 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'Missing or invalid report_id. Must be a positive integer.',
    ]);
    exit();
}

$allowedStatuses = ['Pending', 'Verified', 'Forwarded', 'Ongoing', 'Resolved'];

if ($status === '' || !in_array($status, $allowedStatuses, true)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid or missing status. Allowed values: ' . implode(', ', $allowedStatuses),
    ]);
    exit();
}

// ── VERIFY REPORT EXISTS BEFORE ATTEMPTING SMS ────────────────────────────────
try {
    $reportModel = new Report();
    $report      = $reportModel->getReportById($report_id);

    if (!$report) {
        echo json_encode([
            'success' => false,
            'error'   => "Report #$report_id was not found in the database.",
        ]);
        exit();
    }

    // ── FIRE STATUS-BASED SMS (auto-generates message internally) ─────────────
    SmsNotifier::sendStatusUpdate($report_id, $status);

    echo json_encode([
        'success' => true,
        'message' => "SMS sent successfully for Report #$report_id (Status: $status).",
    ]);
} catch (Exception $e) {
    // Log full detail server-side; return sanitised message to client
    error_log('[send_sms.php] Report #' . $report_id . ' | ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ]);
}
