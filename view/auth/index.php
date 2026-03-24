<?php
require_once dirname(__DIR__, 2) . '/config/init.php';

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
  header('Location: ' . BASE_URL . '/view/user_module/user_dashboard.php');
  exit();
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header('Location: ' . BASE_URL . '/view/admin_module/admin_dashboard.php');
  exit();
} elseif (isset($_SESSION['agency_logged_in']) && $_SESSION['agency_logged_in'] === true) {
  header('Location: ' . BASE_URL . '/view/lgu_module/agency_dashboard.php');
  exit();
}

$error     = $_SESSION['error']      ?? '';
$success   = $_SESSION['success']    ?? '';
$activeTab = $_SESSION['active_tab'] ?? 'login';

unset($_SESSION['error'], $_SESSION['success'], $_SESSION['active_tab']);

if (!in_array($activeTab, ['login', 'register', 'agency'])) {
  $activeTab = 'login';
}

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>AGAP-Link | Authentication</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
  <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/loginstyle.css">
  <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/forgot_password.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <!-- TERMS MODAL -->
  <div id="termsModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeTermsModal()">&times;</span>
      <h2>Terms and Conditions</h2>
      <br>
      <div class="modal-body">
        <h3>Acceptance of Terms</h3>
        <p>By creating an AGAP-Link account and using the platform, you agree to comply with these Terms and Conditions. If you do not agree, please do not use the system.</p>
        <br>
        <h3>Purpose of the Platform</h3>
        <p>AGAP-Link is a community reporting and information platform that allows users to submit reports related to local issues such as public safety, infrastructure concerns, environmental hazards, and community updates.</p>
        <br>
        <h3>User Responsibilities</h3>
        <ul>
          <li>Provide accurate and truthful information in all reports.</li>
          <li>Do not submit false, misleading, or malicious reports.</li>
          <li>Do not upload offensive, abusive, or illegal content.</li>
          <li>Respect the privacy and rights of other individuals.</li>
        </ul>
        <br>
        <h3>Account Security</h3>
        <p>You are responsible for maintaining the confidentiality of your login credentials. AGAP-Link is not liable for unauthorized access resulting from your failure to protect your account information.</p>
        <br>
        <h3>Data Privacy</h3>
        <p>Personal information collected during registration (such as name, email, and contact number) is used solely for account management, communication, and report verification purposes. AGAP-Link will not sell or share your personal data with third parties without consent, except when required by law.</p>
        <br>
        <h3>Content Moderation</h3>
        <p>AGAP-Link administrators reserve the right to review, edit, or remove any report or content that violates these terms, is inappropriate, or is deemed harmful to the community.</p>
        <br>
        <h3>Limitation of Liability</h3>
        <p>AGAP-Link serves as a reporting and communication tool only. The platform does not guarantee immediate response, resolution, or action from authorities. Users acknowledge that submitted reports may require validation and processing time.</p>
        <br>
        <h3>Prohibited Activities</h3>
        <ul>
          <li>Impersonating another person or authority.</li>
          <li>Submitting spam or duplicate reports.</li>
          <li>Attempting to disrupt or hack the system.</li>
          <li>Using the platform for unlawful purposes.</li>
        </ul>
        <br>
        <h3>Termination</h3>
        <p>AGAP-Link reserves the right to suspend or terminate accounts that violate these Terms and Conditions without prior notice.</p>
        <br>
        <h3>Changes to Terms</h3>
        <p>AGAP-Link may update these Terms and Conditions at any time. Continued use of the platform after changes means you accept the revised terms.</p>
        <br>
        <h3>Contact</h3>
        <p>For questions regarding these Terms and Conditions, please contact the AGAP-Link administrators through the official system channels.</p>
        <p style="margin-top:20px;font-weight:600;">By creating an account, you confirm that you have read and agreed to these Terms and Conditions.</p>
      </div>
    </div>
  </div>

  <!-- FORGOT PASSWORD MODAL -->
  <div id="forgotModal">
    <div class="forgot-modal-box">
      <button class="forgot-close" id="forgotCloseBtn" aria-label="Close">&times;</button>

      <div class="step-indicator">
        <div class="step-dot active" id="dot1"></div>
        <div class="step-dot" id="dot2"></div>
        <div class="step-dot" id="dot3"></div>
      </div>

      <!-- Step 1 -->
      <div class="forgot-step active" id="forgotStep1">
        <h3 class="forgot-title">Reset Your Password</h3>
        <p class="forgot-subtitle">Enter the email address linked to your account. We'll help you get back in.</p>
        <div class="forgot-error" id="forgotStep1Error" style="display:none;"></div>
        <input type="email" id="forgotEmail" class="forgot-input" placeholder="name@example.com" autocomplete="email">
        <button class="forgot-btn" id="forgotStep1Btn">Continue</button>
      </div>

      <!-- Step 2 -->
      <div class="forgot-step" id="forgotStep2">
        <h3 class="forgot-title">Send Verification Code</h3>
        <p class="forgot-subtitle">Choose how you want to receive your one-time password (OTP).</p>
        <div class="forgot-error" id="forgotStep2Error" style="display:none;"></div>
        <button class="channel-btn" id="btnSendSms">
          <strong>📱 Send via SMS</strong>
          <span id="maskedPhoneDisplay">to your registered phone</span>
        </button>
        <button class="channel-btn" id="btnSendEmail">
          <strong>📧 Send via Email</strong>
          <span id="maskedEmailDisplay">to your registered email</span>
        </button>
      </div>

      <!-- Step 3 -->
      <div class="forgot-step" id="forgotStep3">
        <h3 class="forgot-title">Enter OTP &amp; New Password</h3>
        <p class="forgot-subtitle">Enter the 6-digit code you received and your new password.</p>
        <div class="forgot-error" id="forgotStep3Error" style="display:none;"></div>
        <div class="forgot-success-msg" id="forgotStep3Success" style="display:none;"></div>
        <div class="otp-countdown">OTP expires in <span id="otpTimer">5:00</span></div>
        <input type="text" id="forgotOtp" class="otp-field" inputmode="numeric" maxlength="6" placeholder="······" autocomplete="one-time-code">
        <input type="password" id="forgotNewPass" class="forgot-input" placeholder="New password (min. 8 characters)">
        <input type="password" id="forgotConfPass" class="forgot-input" placeholder="Confirm new password">
        <button class="forgot-btn" id="forgotStep3Btn">Reset Password</button>
        <button class="resend-link" id="forgotResendLink">&#8617; Resend OTP</button>
      </div>

    </div>
  </div>

  <!-- TOASTS -->
  <?php if ($error): ?>
    <div class="toast toast-error show"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="toast toast-success show"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- MAIN AUTH CONTAINER -->
  <div class="auth-container" id="authContainer">

    <div class="auth-left">
      <div class="overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">
          Welcome to<br>
          <span class="hero-title-highlight">AGAP-Link</span>
        </h1>
        <p>Your centralized platform for community reporting and real-time city updates. Let's build a better place together.</p>
        <small>&copy; 2026 AGAP-Link. All rights reserved.</small>
      </div>
    </div>

    <div class="auth-right">
      <div class="auth-box">
        <h1>Welcome!</h1>
        <p class="subtitle">Enter your credentials to access your account</p>

        <div class="tabs">
          <button class="tab-btn active" id="loginTab" onclick="showTab('login')">Log In</button>
          <button class="tab-btn" id="registerTab" onclick="showTab('register')">Sign Up</button>
          <button class="tab-btn" id="agencyTab" onclick="showTab('agency')">Agency</button>
        </div>

        <!-- LOGIN FORM -->
        <form method="POST" action="<?= BASE_URL ?>/controller/auth_process.php" id="login" class="tab-content active">
          <input type="hidden" name="action" value="login">

          <label>Email</label>
          <input type="email" name="email" placeholder="name@example.com" required>

          <label>
            Password
            <span class="forgot-password" id="forgotPasswordLink">Forgot Password?</span>
          </label>
          <div class="password-wrapper">
            <input type="password" name="password" placeholder="••••••••" id="loginPassword" required>
            <span class="toggle-password" onclick="togglePassword('loginPassword', this)">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>

          <button type="submit">Log In</button>
        </form>

        <!-- REGISTER FORM -->
        <form method="POST" action="<?= BASE_URL ?>/controller/auth_process.php" id="register" class="tab-content">
          <input type="hidden" name="action" value="register">

          <div class="name-row">
            <div class="name-field">
              <label>First Name <span class="required">*</span></label>
              <input type="text" name="first_name" placeholder="Juan"
                value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
            </div>
            <div class="name-field name-field-small">
              <label>M.I.</label>
              <input type="text" name="middle_initial" placeholder="D" maxlength="1"
                value="<?= htmlspecialchars($old['middle_initial'] ?? '') ?>"
                style="text-transform:uppercase;">
            </div>
            <div class="name-field">
              <label>Last Name <span class="required">*</span></label>
              <input type="text" name="last_name" placeholder="Cruz"
                value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
            </div>
          </div>

          <label>Email <span class="required">*</span></label>
          <input type="email" name="email" placeholder="name@example.com"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>

          <label>Contact Number <span class="required">*</span></label>
          <input type="tel" name="phone" placeholder="09123456789"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            pattern="[0-9]*" minlength="11" maxlength="11" inputmode="numeric"
            value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>

          <label>Password <span class="required">*</span></label>
          <div class="password-wrapper">
            <input type="password" name="password" placeholder="••••••••" id="registerPassword" required>
            <span class="toggle-password" onclick="togglePassword('registerPassword', this)">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>

          <label>Confirm Password <span class="required">*</span></label>
          <div class="password-wrapper">
            <input type="password" name="confirm_password" placeholder="••••••••" id="confirmPassword" required>
            <span class="toggle-password" onclick="togglePassword('confirmPassword', this)">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>

          <button type="submit">Create Account</button>

          <p class="terms-text">
            By creating an account, you agree to our<br>
            <span class="terms-link" onclick="openTermsModal()">Terms and Conditions</span>.
          </p>

          <p class="hint" style="font-size:0.75rem;color:#666;margin-top:10px;">
            <span class="required">*</span> Required fields
          </p>
        </form>

        <!-- AGENCY FORM -->
        <form method="POST" action="<?= BASE_URL ?>/controller/agency_login.php" id="agency" class="tab-content">
          <label>Username</label>
          <input type="text" name="username" placeholder="agency_username" required autocomplete="username">

          <label>Password</label>
          <div class="password-wrapper">
            <input type="password" name="password" placeholder="••••••••" id="agencyPassword" required autocomplete="current-password">
            <span class="toggle-password" onclick="togglePassword('agencyPassword', this)">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>

          <button type="submit">Agency Sign In</button>
        </form>

      </div>
    </div>

  </div>

  <script>
    window.__activeTab = "<?= htmlspecialchars($activeTab) ?>";
    window.__baseUrl = "<?= BASE_URL ?>";
  </script>
  <script src="<?= ASSET_URL ?>/js/login/main.js"></script>
  <script src="<?= ASSET_URL ?>/js/login/forgot_password.js"></script>

</body>

</html>