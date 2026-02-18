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

require_once MODEL_PATH . 'Announcement.php';

$announcementModel = new Announcement();
$action = $_POST['action'] ?? '';

// ── CREATE ────────────────────────────────────────────────────────────────────
if ($action === 'create') {
    $title    = trim($_POST['title']   ?? '');
    $content  = trim($_POST['content'] ?? '');
    $admin_id = (int)($_SESSION['admin_id'] ?? 1);

    if (empty($title) || empty($content)) {
        $_SESSION['error'] = 'Title and content are required.';
        header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
        exit();
    }

    $image_path = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = UPLOAD_PATH . 'announcements/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed  = ['image/png', 'image/jpeg', 'image/jpg'];
        $mimeType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            $_SESSION['error'] = 'Invalid image type. Only PNG and JPG are allowed.';
            header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
            exit();
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Image exceeds the 5MB limit.';
            header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
            exit();
        }

        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename   = 'ann_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
            $image_path = $filename;
        }
    }

    if ($announcementModel->create($title, $content, $admin_id, $image_path)) {
        $_SESSION['success'] = 'Announcement published successfully.';
    } else {
        $_SESSION['error'] = 'Failed to publish announcement. Please try again.';
    }

// ── DELETE ────────────────────────────────────────────────────────────────────
} elseif ($action === 'delete') {
    $announcement_id = filter_var($_POST['announcement_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$announcement_id) {
        $_SESSION['error'] = 'Invalid announcement ID.';
        header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
        exit();
    }

    $imagePath = $announcementModel->delete($announcement_id);

    // Delete associated image file if one exists
    if ($imagePath) {
        $filePath = UPLOAD_PATH . 'announcements/' . basename($imagePath);
        if (file_exists($filePath)) unlink($filePath);
    }

    $_SESSION['success'] = 'Announcement deleted.';
}

header("Location: " . BASE_URL . "/view/admin_module/announcement.php");
exit();
