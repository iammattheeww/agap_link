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
    header("Location: " . BASE_URL . "/view/user_module/user_dashboard.php");
    exit();
}

$action = $_POST['action'] ?? 'create_report';

switch ($action) {
    case 'create_report':
        create_report();
        break;
    default:
        $_SESSION['error'] = "Invalid action!";
        header("Location: " . BASE_URL . "/view/user_module/user_dashboard.php?report_error=1");
        exit();
}

// ─── CATEGORY → AGENCY AUTO-ASSIGNMENT ──────────────────────────────────
// Uses model method getAgencyByCategory() to look up the primary agency

// CREATE REPORT FUNCTION
function create_report()
{
    global $conn;
    $reportModel = new Report();

    $user_id     = (int) $_SESSION['user_id'];
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $description = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address']     ?? '');

    $gps_lat  = filter_input(INPUT_POST, 'gps_lat',  FILTER_VALIDATE_FLOAT);
    $gps_long = filter_input(INPUT_POST, 'gps_long', FILTER_VALIDATE_FLOAT);
    $gps_lat  = ($gps_lat  !== false) ? (float) $gps_lat  : null;
    $gps_long = ($gps_long !== false) ? (float) $gps_long : null;

    // FILE UPLOAD
  // FILE UPLOAD
$photo_path = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    // Ensure upload directory exists
    $upload_dir = rtrim(UPLOAD_PATH, '/') . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_tmp  = $_FILES['photo']['tmp_name'];
    $file_name = $_FILES['photo']['name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Only allow png, jpg, jpeg
    $allowed_exts = ['png', 'jpg', 'jpeg'];

    // Check file extension
    if (!in_array($file_ext, $allowed_exts, true)) {
        error_log("Invalid file extension: $file_ext");
    } else {
        // Check MIME type for safety
        $file_mime = mime_content_type($file_tmp);
        $allowed_mimes = ['image/png', 'image/jpeg'];

        if (!in_array($file_mime, $allowed_mimes, true)) {
            error_log("Invalid MIME type: $file_mime");
        } else {
            // Normalize extension: always save JPEG as jpg
            if ($file_ext === 'jpeg') $file_ext = 'jpg';

            $filename   = uniqid('report_', true) . '.' . $file_ext;
            $targetPath = $upload_dir . $filename;

            if (move_uploaded_file($file_tmp, $targetPath)) {
                $photo_path = rtrim(UPLOAD_URL, '/') . '/' . $filename;
            } else {
                error_log("Failed to move uploaded file to $targetPath");
            }
        }
    }
} elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    error_log("File upload error code: " . $_FILES['photo']['error']);
}

    // AUTO-ASSIGN AGENCY BASED ON CATEGORY (via model)
    $reportModel = new Report();
    $assigned_agency_id = $reportModel->getAgencyByCategory((int)$category_id);

    try {
        $report_id = $reportModel->createReport(
            $user_id, $category_id, $description, $address,
            $photo_path, $gps_lat, $gps_long,
            'Medium', $assigned_agency_id
        );

        if ($report_id) {
            header("Location: " . BASE_URL . "/view/user_module/user_dashboard.php?report_success=1");
            exit();
        } else {
            throw new Exception("Failed to create report");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $_SESSION['error'] = 'Submission failed: ' . $e->getMessage();
        header("Location: " . BASE_URL . "/view/user_module/user_dashboard.php?report_error=1");
        exit();
    }
}
