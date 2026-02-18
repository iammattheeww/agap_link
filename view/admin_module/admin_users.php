<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'User.php';

$userObj = new User();
$users   = $userObj->list_users();

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
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

        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <main class="main-content page-transition">

            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">User Management</h1>
                    <p class="welcome-subtitle">Manage all registered users of the platform.</p>
                </div>
                <!-- Add User button intentionally removed (ADMIN-6) -->
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="users-search">
                <input type="text" id="userSearchInput" placeholder="Search users by name or email..." oninput="filterUsers()">
            </div>

            <section class="users-section">
                <div class="table-wrapper">
                    <table class="users-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="user-row">
                                        <td>
                                            <div class="user-info">
                                                <span class="user-fullname"><?= htmlspecialchars($user['full_name']) ?></span>
                                                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['phone_number'] ?? '—') ?></td>
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="user-actions">
                                                <!-- Edit button removed (ADMIN-7) -->
                                                <button
                                                    class="action-delete"
                                                    onclick="openDeleteModal(<?= $user['user_id'] ?>, '<?= htmlspecialchars(addslashes($user['full_name'])) ?>')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
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

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="report-modal-overlay" id="deleteModal">
        <div class="report-modal" style="max-width: 480px; min-width: unset; width: 90%;">
            <div class="modal-header">
                <h2 style="font-family: var(--font-display); color: var(--color-secondary);">Delete User</h2>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <p style="color: var(--color-gray-600); margin: 10px 0 20px;">
                Are you sure you want to delete <strong id="deleteUserName"></strong>?
                This action cannot be undone. Their reports will also be deleted.
            </p>
            <form method="POST" action="<?= BASE_URL ?>/controller/admin_user_process.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
                <div class="form-actions" style="margin-top: 0;">
                    <button type="submit" class="btn-primary" style="background:#dc2626;">Yes, Delete</button>
                    <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>

    <script>
        function openDeleteModal(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        function filterUsers() {
            const q = document.getElementById('userSearchInput').value.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }
    </script>
</body>

</html>
