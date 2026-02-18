<?php
require_once dirname(__DIR__) . "/config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
    exit();
}

require_once dirname(__DIR__) . "/config/agaplinkdb.php";

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $admin_id = $_SESSION['admin_id'] ?? 1;

    if (empty($title) || empty($content)) {
        $_SESSION['error'] = 'Title and content are required.';
        header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
        exit();
    }

    $image_path = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = UPLOAD_PATH . 'announcements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = ['image/png', 'image/jpeg', 'image/jpg'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowed)) {
            $_SESSION['error'] = 'Invalid image type. Only PNG and JPG allowed.';
            header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
            exit();
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Image too large. Maximum 5MB.';
            header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
            exit();
        }

        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'ann_' . uniqid() . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $image_path = $filename;
        }
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO announcements (title, content, image_path, created_by) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$title, $content, $image_path, $admin_id]);
        $_SESSION['success'] = 'Announcement published successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to publish announcement. Please try again.';
    }

} elseif ($action === 'delete') {
    $announcement_id = filter_var($_POST['announcement_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$announcement_id) {
        $_SESSION['error'] = 'Invalid announcement ID.';
        header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
        exit();
    }

    try {
        // Get image path first to delete the file
        $imgStmt = $conn->prepare("SELECT image_path FROM announcements WHERE announcement_id = ?");
        $imgStmt->execute([$announcement_id]);
        $row = $imgStmt->fetch();

        if (!empty($row['image_path'])) {
            $filePath = UPLOAD_PATH . 'announcements/' . basename($row['image_path']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->execute([$announcement_id]);
        $_SESSION['success'] = 'Announcement deleted.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete announcement.';
    }
}

header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
exit();
