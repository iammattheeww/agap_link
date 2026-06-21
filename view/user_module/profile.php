<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'User.php';

$userModel   = new User();
$userDetails = $userModel->get_user_details($_SESSION['user_id']);

$firstName     = $userDetails['first_name']     ?? '';
$middleInitial = $userDetails['middle_initial'] ?? '';
$lastName      = $userDetails['last_name']      ?? '';
$email         = $userDetails['email']          ?? '';
$phoneNumber   = $userDetails['phone_number']   ?? '';
$fullName      = $userDetails['full_name']      ?? '';
$firstInitial  = !empty($firstName) ? strtoupper(substr($firstName, 0, 1)) : 'U';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/favicon_io/favicon.ico">
    <title>Profile - AGAP-Link</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/user_module/user_module.css">
</head>

<body>
    <?php require VIEW_PATH . 'partials/mobile_topnav_user.php'; ?>

    <div class="dashboard-container">
        <?php require VIEW_PATH . 'partials/user_sidebar.php' ?>

        <main class="main-content page-transition">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success fade-alert" style="margin:20px;">
                    <?= htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- TWO-COLUMN LAYOUT -->
            <div class="profile-two-col">

                <!-- LEFT: Profile Card -->
                <div class="profile-left-col">
                    <div class="profile-card-ui">
                        <div class="profile-avatar-ui"><?= $firstInitial ?></div>
                        <div class="profile-name-ui"><?= htmlspecialchars($fullName) ?></div>
                        <div class="profile-meta-ui">Resident</div>
                    </div>
                </div>

                <!-- RIGHT: Forms stacked vertically -->
                <div class="profile-right-col">

                    <!-- ACCOUNT INFORMATION -->
                    <div class="profile-form-card">
                        <h1 class="welcome-title">Account Information</h1>
                        <br>
                        <form id="profileInfoForm" action="<?= BASE_URL ?>/controller/profile_process.php" method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-grid form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-input"
                                        value="<?= htmlspecialchars($firstName) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Middle Initial</label>
                                    <input type="text"
                                        name="middle_initial"
                                        class="form-input form-input-small"
                                        value="<?= htmlspecialchars($middleInitial) ?>"
                                        maxlength="1"
                                        style="text-transform: uppercase;"
                                        oninput="this.value = this.value.toUpperCase().slice(0,1);">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-input"
                                        value="<?= htmlspecialchars($lastName) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-input"
                                        value="<?= htmlspecialchars($email) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text"
                                        name="phone_number"
                                        class="form-input"
                                        value="<?= htmlspecialchars($phoneNumber) ?>"
                                        maxlength="11"
                                        pattern="\d{11}"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/\D/g, '')"
                                        placeholder="09123456789"
                                        required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Save Changes</button>
                                <button type="reset" class="btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- CHANGE PASSWORD -->
                    <div class="profile-form-card">
                        <h2 class="form-section-title">Change Password</h2>
                        <form id="passwordForm" action="<?= BASE_URL ?>/controller/profile_process.php" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-input" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- DELETE ACCOUNT -->
                    <div class="profile-form-card danger-zone">
                        <h2 class="form-section-title text-danger">Delete Account</h2>
                        <p class="danger-text">This action permanently deletes your account and all data.</p>
                        <button class="btn-danger" onclick="openDeleteModal()">Delete Account</button>
                    </div>

                </div><!-- /profile-right-col -->
            </div><!-- /profile-two-col -->

        </main>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">⚠</div>
                <h2 class="modal-title">Delete Account</h2>
            </div>
            <div class="modal-body">
                <div class="modal-warning">
                    <div class="modal-warning-title">Warning: This action is permanent!</div>
                    <div class="modal-warning-text">
                        All your data including reports, photos, and personal information will be permanently deleted and cannot be recovered.
                    </div>
                </div>
                <p style="margin-bottom: 15px; color: var(--color-gray-800);">
                    To confirm deletion, please type <strong style="color: var(--color-secondary);">DELETE</strong> in the field below:
                </p>
                <form id="deleteAccountForm" action="<?= BASE_URL ?>/controller/profile_process.php" method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="form-group">
                        <input type="text" id="delete_confirmation" name="delete_confirmation"
                            class="form-input" placeholder="Type DELETE to confirm" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                        <button type="submit" class="btn-danger" id="confirmDeleteBtn" disabled>Yes, Delete My Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/user_module/main.js"></script>
</body>

</html>