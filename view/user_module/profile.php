<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: /agap_link/view/auth/index.php");
    exit();
}

require_once __DIR__ . '/../../model/User.php';

// INITIALIZE USER MODEL
$userModel = new User();

// FETCH USER DETAILS
$userDetails = $userModel->get_user_details($_SESSION['user_id']);

// Extract user data
$firstName = $userDetails['first_name'] ?? '';
$middleInitial = $userDetails['middle_initial'] ?? '';
$lastName = $userDetails['last_name'] ?? '';
$email = $userDetails['email'] ?? '';
$phoneNumber = $userDetails['phone_number'] ?? '';
$fullName = $userDetails['full_name'] ?? '';

// Get first initial for avatar
$firstInitial = !empty($firstName) ? strtoupper(substr($firstName, 0, 1)) : 'U';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/favicon_io/favicon.ico">
    <title>Profile - AGAP-Link</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/user_module/user_module.css">
    <style>
        /* DELETE ACCOUNT SECTION STYLES */
        .danger-zone {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: var(--radius-lg);
            padding: 25px;
            margin-top: 30px;
        }

        .danger-zone-title {
            color: #991b1b;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: var(--font-display);
        }

        .danger-zone-description {
            color: #7f1d1d;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .btn-danger {
            background-color: #dc2626;
            color: var(--color-white);
            border: none;
            padding: 12px 32px;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-danger:hover {
            background-color: #991b1b;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: var(--color-white);
            margin: 10% auto;
            padding: 30px;
            border-radius: var(--radius-lg);
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            animation: slideIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--color-gray-200);
        }

        .modal-icon {
            width: 50px;
            height: 50px;
            background-color: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #dc2626;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-secondary);
            font-family: var(--font-display);
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius-md);
        }

        .modal-warning-title {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 5px;
        }

        .modal-warning-text {
            color: #78350f;
            font-size: 0.95rem;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background-color: var(--color-white);
            color: var(--color-gray-600);
            border: 2px solid var(--color-gray-300);
        }

        .btn-cancel:hover {
            border-color: var(--color-gray-600);
            background-color: var(--color-gray-100);
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <?php require VIEW_PATH . 'partials/user_sidebar.php' ?>
<div class="page-transition">
        <!-- MAIN CONTENT -->
    <main class="main-content">
  <!-- SUCCESS OUTSIDE LAYOUT -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success fade-alert" style="margin:20px;">
      <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <div class="profile-settings-wrapper">

    <!-- TOP ROW -->
    <div class="profile-top-grid">

      <!-- PROFILE CARD -->
      <div class="profile-card-ui">
        <div class="profile-avatar-ui"><?= $firstInitial ?></div>
        <div class="profile-name-ui"><?= htmlspecialchars($fullName) ?></div>
        <div class="profile-meta-ui">Resident</div>
        <button class="btn-secondary">Edit Profile Photo</button>
      </div>

      <!-- ACCOUNT INFO -->
      <div class="profile-form-card">
        <h2 class="form-section-title">Account Information</h2>

        <form action="/agap_link/controller/profile_process.php" method="POST">
          <input type="hidden" name="action" value="update_profile">

          <div class="form-grid form-grid-2">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-input"
                value="<?= htmlspecialchars($firstName) ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Middle Initial</label>
              <input type="text" name="middle_initial" class="form-input form-input-small"
                value="<?= htmlspecialchars($middleInitial) ?>">
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
              <input type="tel" name="phone_number" class="form-input"
                value="<?= htmlspecialchars($phoneNumber) ?>" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes</button>
            <button type="reset" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>

    </div>

    <!-- CHANGE PASSWORD FULL WIDTH -->
    <div class="profile-full-width">
      <div class="profile-form-card">
        <h2 class="form-section-title">Change Password</h2>

        <form action="/agap_link/controller/profile_process.php" method="POST">
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
    </div>

    <!-- DELETE FULL WIDTH -->
    <div class="profile-full-width">
      <div class="profile-form-card danger-zone">
        <h2 class="form-section-title text-danger">Delete Account</h2>

        <p class="danger-text">
          This action permanently deletes your account and all data.
        </p>

        <button class="btn-danger" onclick="openDeleteModal()">
          Delete Account
        </button>
      </div>
    </div>

  </div>
</main>



    </div>
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
                <form action="/agap_link/controller/profile_process.php" method="POST" id="deleteAccountForm">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="form-group">
                        <input
                            type="text"
                            id="delete_confirmation"
                            name="delete_confirmation"
                            class="form-input"
                            placeholder="Type DELETE to confirm"
                            required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary btn-cancel" onclick="closeDeleteModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn-danger" id="confirmDeleteBtn" disabled>
                            Yes, Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/agap_link/assets/js/user_module/main.js"></script>
    <script src="/agap_link/assets/js/user_module/profile.js"></script>
    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

</html>