<?php
session_start();

// PREVENT ACCESS IF NOT LOGGED IN
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /agap_link/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/user.php';

$userObj = new User();
$users = $userObj->list_users();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - AGAP-Link</title>
    <!-- USE SAME DASHBOARD CSS -->
    <link rel="stylesheet" href="/agap_link/assets/css/admin_module/admin_module.css">
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
            <a href="add_user.php" class="btn-report-issue" style="text-decoration:none;">Add User</a>
        </header>

        <!-- SEARCH BAR -->
        <div class="users-search" style="margin: 20px 0;">
            <input type="text" placeholder="Search users by name or email..." style="width:100%; max-width:400px; padding:12px 16px; border-radius:8px; border:1px solid #ddd;">
        </div>

        <!-- USERS TABLE -->
        <section class="recent-reports-section">
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
                                    <span class="role-badge"><?= $user['role'] ?? 'Resident' ?></span>
                                </td>
                                <td>
                                    <?php $status = $user['status'] ?? 'active'; ?>
                                    <span class="<?= $status === 'active' ? 'status-active' : 'status-inactive' ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('M Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="action-edit">Edit</a>
                                        <a href="delete_user.php?id=<?= $user['user_id'] ?>" 
                                           onclick="return confirm('Delete this user?');"
                                           class="action-delete">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

<script src="/agap_link/assets/js/user_module/main.js"></script>
<button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>
</html>
