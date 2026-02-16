<?php
require_once dirname(__DIR__) . "/config/init.php";
require_once MODEL_PATH . 'Report.php';

// AUTH CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

// REQUEST METHOD CHECK
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/user_module/create_report.php");
    exit();
}

$action = $_POST['action'] ?? 'create_report';

switch ($action) {
    case 'create_report':
        create_report();
        break;

    default:
        $_SESSION['error'] = "Invalid action!";
        header("Location: " . BASE_URL . "/view/user_module/create_report.php");
        exit();
}

// CREATE REPORT FUNCTION
function create_report()
{
    $report = new Report();

    // THE REQUIRED DATA FOR CREATING A REPORT
    $user_id     = (int) $_SESSION['user_id'];
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $description = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address'] ?? '');

    // GEOLOCATION (MAKITA NI SA JAVASCRIPT NATON)
    $gps_lat  = filter_input(INPUT_POST, 'gps_lat', FILTER_VALIDATE_FLOAT);
    $gps_long = filter_input(INPUT_POST, 'gps_long', FILTER_VALIDATE_FLOAT);

    $gps_lat  = ($gps_lat !== false) ? (float) $gps_lat : null;
    $gps_long = ($gps_long !== false) ? (float) $gps_long : null;

    // FILE UPLOAD
    $photo_path = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = UPLOAD_PATH;

        if (!is_dir($upload_dir)) {
            // FIXED: Using standard 0755 to prevent 403 Forbidden on live servers
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed, true)) {
            $filename   = uniqid('report_', true) . '.' . $file_ext;
            $targetPath = $upload_dir . $filename;

            if (move_uploaded_file($file_tmp, $targetPath)) {
                $photo_path = UPLOAD_URL . '/' . $filename;
            }
        }
    }

    try {
        $report_id = $report->createReport($user_id, $category_id, $description, $address, $photo_path, $gps_lat, $gps_long);

        if ($report_id) {
            header("Location: " . BASE_URL . "/view/user_module/create_report.php?success=1");
            exit();
        } else {
            throw new Exception("Failed to create report");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $_SESSION['error'] = 'Submission failed: ' . $e->getMessage();
        header("Location: " . BASE_URL . "/view/user_module/create_report.php");
        exit();
    }
}
