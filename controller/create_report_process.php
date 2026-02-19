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
// Maps category_id to agency name (matches agencies.name in DB)
function getAgencyForCategory(int $category_id): ?string
{
    $map = [
        1 => 'DPWH',          // Infrastructure
        2 => 'CENRO',         // Waste Management
        3 => 'LWUA',          // Water & Sanitation
        4 => 'PNP',           // Public Safety
        5 => 'DENR',          // Environment
        6 => 'MERALCO',       // Utilities
        7 => 'LTO',           // Traffic
        8 => 'DOH',           // Public Health
        9 => 'Brgy. Granada', // Community Facilities → Barangay/LGU
    ];
    return $map[$category_id] ?? 'Brgy. Granada';
}

// Lookup agency_id from agencies table by name
function resolveAgencyId(\PDO $conn, string $agencyName): ?int
{
    $stmt = $conn->prepare("SELECT agency_id FROM agencies WHERE name = ? LIMIT 1");
    $stmt->execute([$agencyName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['agency_id'];

    // Fallback: try Brgy. Granada (agency_id 0)
    $stmt2 = $conn->prepare("SELECT agency_id FROM agencies ORDER BY agency_id ASC LIMIT 1");
    $stmt2->execute();
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    return $row2 ? (int) $row2['agency_id'] : null;
}

// CREATE REPORT FUNCTION
function create_report()
{
    global $conn;
    $report = new Report();

    $user_id     = (int) $_SESSION['user_id'];
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $description = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address']     ?? '');

    $gps_lat  = filter_input(INPUT_POST, 'gps_lat',  FILTER_VALIDATE_FLOAT);
    $gps_long = filter_input(INPUT_POST, 'gps_long', FILTER_VALIDATE_FLOAT);
    $gps_lat  = ($gps_lat  !== false) ? (float) $gps_lat  : null;
    $gps_long = ($gps_long !== false) ? (float) $gps_long : null;

    // FILE UPLOAD
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = UPLOAD_PATH;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed, true)) {
            $filename   = uniqid('report_', true) . '.' . $file_ext;
            $targetPath = $upload_dir . $filename;
            if (move_uploaded_file($file_tmp, $targetPath)) {
                $photo_path = UPLOAD_URL . '/' . $filename;
            }
        }
    }

    // AUTO-ASSIGN AGENCY BASED ON CATEGORY
    $agencyName = getAgencyForCategory($category_id);
    $assigned_agency_id = resolveAgencyId($conn, $agencyName);

    try {
        $report_id = $report->createReport(
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
