<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);

// DESTROY ALL SESSION DATA
session_destroy();
session_unset();
unset($_SESSION["logged_in"]);
$_SESSION = array();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AGAP-Link | Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <link rel="stylesheet" href="/agap_link/assets/css/login/loginstyle.css">
</head>

<body>

    <div class="auth-container" id="authContainer">

        <!-- LEFT HERO -->
        <div class="auth-left">
            <div class="overlay"></div>
            <div class="hero-content">
                <h1 class="hero-title">
                    Welcome to<br>
                    <span class="hero-title-highlight">AGAP-Link</span>
                </h1>
                <p>
                    Your centralized platform for community
                    reporting and real-time city updates. Let's build a better place
                    together.
                </p>

                <small>© 2026 AGAP-Link. All rights reserved.</small>
            </div>
        </div>

        <!-- RIGHT FORM -->
        <div class="auth-right">
            <div class="auth-box">
                <h1>Welcome!</h1>
                <p class="subtitle">Enter your credentials to access your account</p>

                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- TABS -->
                <div class="tabs">
                    <button class="tab-btn active" id="loginTab" onclick="showTab('login')">Log In</button>
                    <button class="tab-btn" id="registerTab" onclick="showTab('register')">Sign Up</button>
                </div>

                <!-- LOGIN -->
                <form method="POST" action="../../controller/auth_process.php" id="login" class="tab-content active">
                    <input type="hidden" name="action" value="login">

                    <label>Email</label>
                    <input type="email" name="email" placeholder="name@example.com" required>

                    <label>Password <span class="forgot-password"> Forgot Password?</span></label>
                    <input type="password" name="password" placeholder="••••••••" required>

                    <button type="submit">Log In</button>

                    <p class="hint">Don’t have an account? <span class="create-account" onclick="showTab('register')">Create Account</span></p>
                </form>

                <!-- REGISTER -->
                <form method="POST" action="../../controller/auth_process.php" id="register" class="tab-content">
                    <input type="hidden" name="action" value="register">

                    <!-- NAME FIELDS ROW -->
                    <div class="name-row">
                        <div class="name-field">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" placeholder="Juan" required>
                        </div>

                        <div class="name-field name-field-small">
                            <label>M.I.</label>
                            <input type="text" name="middle_initial" placeholder="D" maxlength="5"
                                style="text-transform: uppercase;">
                        </div>

                        <div class="name-field">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" placeholder="Cruz" required>
                        </div>
                    </div>

                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="name@example.com" required>

                    <label>Contact Number <span class="required">*</span></label>
                    <input type="tel" name="phone" placeholder="09123456789" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]*" minlength="11" maxlength="11" inputmode="numeric" required>

                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" required>

                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" required>

                    <button type="submit">Create Account</button>

                    <p class="hint" style="font-size: 0.75rem; color: #666; margin-top: 10px;">
                        <span class="required">*</span> Required fields
                    </p>
                </form>

            </div>
        </div>

    </div>

    <script src="/agap_link/assets/js/login/main.js"></script>
</body>

</html>