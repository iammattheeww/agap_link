<?php
require_once dirname(__DIR__) . "/config/init.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/view/admin_module/admin_users.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'delete') {
    $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$user_id) {
        $_SESSION['error'] = 'Invalid user ID.';
        header("Location: " . BASE_URL . "/view/admin_module/admin_users.php");
        exit();
    }

    require_once dirname(__DIR__) . "/config/agaplinkdb.php";

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'User deleted successfully.';
        } else {
            $_SESSION['error'] = 'User not found.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to delete user. Please try again.';
    }
}

header("Location: " . BASE_URL . "/view/admin_module/admin_users.php");
exit();
