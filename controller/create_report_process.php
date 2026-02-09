<?php
session_start();
require_once __DIR__ . '/../config/agaplinkdb.php';


/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    header("Location: /agap_link/view/auth/index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Request method check
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /agap_link/view/user_module/create_report.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Required data
|--------------------------------------------------------------------------
*/
$user_id     = (int) $_SESSION['user_id'];
$category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
$description = trim($_POST['description'] ?? '');
$address     = trim($_POST['address'] ?? '');

/*
|--------------------------------------------------------------------------
| GPS (FILTER_VALIDATE_FLOAT returns FALSE)
|--------------------------------------------------------------------------
*/
$gps_lat  = filter_input(INPUT_POST, 'gps_lat', FILTER_VALIDATE_FLOAT);
$gps_long = filter_input(INPUT_POST, 'gps_long', FILTER_VALIDATE_FLOAT);

$gps_lat  = ($gps_lat !== false) ? (float) $gps_lat : null;
$gps_long = ($gps_long !== false) ? (float) $gps_long : null;

/*
|--------------------------------------------------------------------------
| File upload
|--------------------------------------------------------------------------
*/
$photo_path = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['photo']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($file_ext, $allowed, true)) {
        $filename   = uniqid('report_', true) . '.' . $file_ext;
        $targetPath = $upload_dir . $filename;

        if (move_uploaded_file($file_tmp, $targetPath)) {
            $photo_path = '/agap_link/uploads/' . $filename;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Report status (required by DB schema)
|--------------------------------------------------------------------------
*/
$status = 'pending';
/*
|--------------------------------------------------------------------------
| SQL insert
|--------------------------------------------------------------------------
*/
$sql = "INSERT INTO reports
        (user_id, category_id, description, address, photo_path, gps_lat, gps_long, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new PDOException("Prepare failed");
    }

    /*
    |--------------------------------------------------------------------------
    | Bind parameters
    |--------------------------------------------------------------------------
    */
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $category_id, PDO::PARAM_INT);
    $stmt->bindParam(3, $description, PDO::PARAM_STR);
    $stmt->bindParam(4, $address, PDO::PARAM_STR);
    $stmt->bindParam(5, $photo_path, PDO::PARAM_STR);
    $stmt->bindParam(6, $gps_lat, PDO::PARAM_STR); // Use STR for NULL floats
    $stmt->bindParam(7, $gps_long, PDO::PARAM_STR);
    $stmt->bindParam(8, $status, PDO::PARAM_STR);

    /*
    |--------------------------------------------------------------------------
    | Execute
    |--------------------------------------------------------------------------
    */
    if ($stmt->execute()) {
        header("Location: /agap_link/view/user_module/create_report.php?success=1");
        exit();
    } else {
        throw new PDOException("Execute failed");
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = 'Submission failed: ' . $e->getMessage();
    header("Location: /agap_link/view/user_module/create_report.php");
    exit();
}
