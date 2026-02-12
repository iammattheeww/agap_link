<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT ACCESS IF NOT LOGGED IN
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'User.php';

$userObj = new User();
$users   = $userObj->list_users();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>User Management - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_module.css">
</head>

<body>
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">

            <!-- HEADER -->
            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">User Management</h1>
                    <p class="welcome-subtitle">Manage all registered users of the platform.</p>
                </div>
                <a href="add_user.php" class="btn-report-issue">+ Add User</a>
            </header>

            <!-- SEARCH BAR -->
            <div class="users-search">
                <input type="text" placeholder="Search users by name or email...">
            </div>

            <!-- USERS TABLE -->
            <section class="users-section">
                <div class="table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <span class="user-fullname"><?= htmlspecialchars($user['full_name']) ?></span>
                                                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge"><?= htmlspecialchars($user['role'] ?? 'Resident') ?></span>
                                        </td>
                                        <td>
                                            <?php $status = $user['status'] ?? 'active'; ?>
                                            <span class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="user-actions">
                                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="action-edit">Edit</a>
                                                <a href="delete_user.php?id=<?= $user['user_id'] ?>"
                                                    onclick="return confirm('Delete this user? This action cannot be undone.');"
                                                    class="action-delete">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon">👥</div>
                                            <p class="empty-message">No users found.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
</body>

</html>