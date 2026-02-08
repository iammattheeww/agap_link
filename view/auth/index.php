<?php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AGAP-Link | Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/agap_link/assets/favicon_io/favicon.ico">
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

                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="John Doe" required>

                    <label>Email</label>
                    <input type="email" name="email" placeholder="name@example.com" required>

                    <label>Contact Number</label>
                    <input type="tel" name="phone" placeholder="09123456789" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]*" minlength="11" maxlength="11" inputmode="numeric" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>

                    <button type="submit">Create Account</button>
                </form>

            </div>
        </div>

    </div>

    <script src="/agap_link/assets/js/login/main.js"></script>
</body>

</html>