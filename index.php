<?php
require_once __DIR__ . '/config/init.php';

$page    = isset($_GET['page']) ? $_GET['page'] : '';
$subpage = isset($_GET['subpage']) ? $_GET['subpage'] : '';
$action  = isset($_GET['action']) ? $_GET['action'] : '';
$id      = isset($_GET['id']) ? $_GET['id'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/favicon_io/favicon.ico">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landing_page/style.css">
    <script src="<?= BASE_URL ?>/assets/js/landing/main.js"></script>
</head>

<body>
    <?php
    $page = $_GET['page'] ?? 'landing'; // SAME AS $page = isset($_GET['page']) ? $_GET['page'] : 'landing';

    switch ($page) {
        case 'login':
            require VIEW_PATH . '/auth/index.php';
            break;

        case 'user-dashboard':
            require VIEW_PATH . '/user_module/user_dashboard.php';
            break;

        case 'admin-dashboard':
            require VIEW_PATH . '/admin_module/admin_dashboard.php';
            break;

        default:
            require VIEW_PATH . '/landing_module/index.php';
            break;
    }
    ?>
</body>

</html>