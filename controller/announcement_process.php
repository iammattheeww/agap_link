<?php
require_once dirname(__DIR__) . '/config/init.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
    exit();
}

require_once MODEL_PATH . 'Announcement.php';
require_once MODEL_PATH . 'SmsNotifier.php';

$announcementModel = new Announcement();
$action = $_POST['action'] ?? '';

// ── CREATE ────────────────────────────────────────────────────────────────────
if ($action === 'create') {
    $title    = trim($_POST['title']    ?? '');
    $content  = trim($_POST['content']  ?? '');
    $category = trim($_POST['category'] ?? '');
    $admin_id = (int) ($_SESSION['admin_id'] ?? 1);

    if (empty($title) || empty($content) || empty($category)) {
        $_SESSION['error'] = 'Title, content, and category are required.';
        header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
        exit();
    }

    $image_path = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = UPLOAD_PATH . 'announcements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed  = ['image/png', 'image/jpeg', 'image/jpg'];
        $mimeType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            $_SESSION['error'] = 'Invalid image type. Only PNG and JPG are allowed.';
            header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
            exit();
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Image exceeds the 5MB limit.';
            header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
            exit();
        }

        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'ann_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
            $image_path = $filename;
        }
    }

    if ($announcementModel->create($title, $content, $admin_id, $image_path)) {
        $_SESSION['success'] = 'Announcement published successfully.';

        // SMS BLAST via model — SHORT MESSAGE TO AVOID SPAM FILTERS
        require_once MODEL_PATH . 'User.php';
        $userModel = new User();
        $phonesArray = $userModel->getAllPhoneNumbers();

        // Include title, category, content, and promotional message
        $titleTrimmed = trim($title);
        $contentTrimmed = trim($content);
        $categoryLabel = trim($category);
        
        // Truncate content to fit in SMS while including category and promotion
        // Target: 2-3 SMS segments for good readability
        $maxContentLen = max(80, 300 - strlen($titleTrimmed) - strlen($categoryLabel) - 80);
        
        if (mb_strlen($contentTrimmed) > $maxContentLen) {
            $contentTrimmed = mb_substr($contentTrimmed, 0, $maxContentLen - 3) . '...';
        }
        
        $smsBody = "AGAP-Link: [$categoryLabel]\n" . 
                   "$titleTrimmed\n\n" . 
                   "$contentTrimmed\n\n" .
                   "Stay informed. Download AGAP-Link now!";

        if (!empty($phonesArray)) {
            try {
                // Format as comma-separated string for PhilSMS API bulk send
                $phonesCsv = implode(',', array_map(function($p) {
                    return is_array($p) ? ($p['phone_number'] ?? '') : $p;
                }, $phonesArray));

                if (!empty($phonesCsv)) {
                    SmsNotifier::sendRawSMS($phonesCsv, $smsBody);
                    $_SESSION['success'] .= ' SMS sent to all users.';
                }
            } catch (Exception $e) {
                error_log('[announcement_process] SMS blast failed: ' . $e->getMessage());
                $_SESSION['warning'] = 'Announcement published, but SMS notification failed.';
            }
        }
    } else {
        $_SESSION['error'] = 'Failed to publish announcement. Please try again.';
    }

    // ── DELETE ────────────────────────────────────────────────────────────────────
} elseif ($action === 'delete') {
    $announcement_id = filter_var($_POST['announcement_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$announcement_id) {
        $_SESSION['error'] = 'Invalid announcement ID.';
        header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
        exit();
    }

    $imagePath = $announcementModel->delete($announcement_id);

    if ($imagePath) {
        $filePath = UPLOAD_PATH . 'announcements/' . basename($imagePath);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $_SESSION['success'] = 'Announcement deleted.';
}

header('Location: ' . BASE_URL . '/view/admin_module/announcement.php');
exit();
