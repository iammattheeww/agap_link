<?php
require_once dirname(__DIR__) . "/config/init.php";
require_once MODEL_PATH . 'Report.php';

header('Content-Type: application/json');

// SECURITY
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["labels" => [], "data" => []]);
    exit();
}

$reportModel = new Report();
$userReports = $reportModel->getAllReports();

$filter = $_GET['filter'] ?? 'weekly';

$data = [];
$labels = [];

switch ($filter) {

    // ========================
    // DAILY (Last 7 Days)
    // ========================
case 'daily':
    $labels = [];
    $data = array_fill(0, 24, 0);

    for ($i = 0; $i < 24; $i++) {
        $labels[] = date('g A', strtotime("$i:00"));
    }

    $today = date('Y-m-d');

    foreach ($userReports as $report) {
        if (!empty($report['created_at'])) {
            $reportDate = date('Y-m-d', strtotime($report['created_at']));

            if ($reportDate === $today) {
                $hour = (int) date('G', strtotime($report['created_at']));
                $data[$hour]++;
            }
        }
    }
    break;
    // ========================
    // WEEKLY
    // ========================
   case 'weekly':
    $labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $data = array_fill(0, 7, 0);

    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    foreach ($userReports as $report) {
        if (!empty($report['created_at'])) {
            $date = date('Y-m-d', strtotime($report['created_at']));

            if ($date >= $startOfWeek && $date <= $endOfWeek) {
                $dayIndex = date('N', strtotime($date)) - 1;
                $data[$dayIndex]++;
            }
        }
    }
    break;

    // ========================
    // MONTHLY
    // ========================
    case 'monthly':
        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $data = array_fill(0, 12, 0);

        foreach ($userReports as $report) {
            if (!empty($report['created_at'])) {
                $monthIndex = date('n', strtotime($report['created_at'])) - 1;
                $data[$monthIndex]++;
            }
        }
        break;
}

echo json_encode([
    "labels" => $labels,
    "data" => $data
]);