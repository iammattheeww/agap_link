<?php
require_once dirname(__DIR__, 2) . '/config/init.php';

if (!isset($_SESSION['pending_login_user_id'])) {
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: ' . BASE_URL . '/view/user_module/user_dashboard.php');
    exit();
}

$maskedPhone   = $_SESSION['pending_login_masked_phone'] ?? '09*****XXX';
$verifyError   = $_SESSION['verify_error']   ?? '';
$verifySuccess = $_SESSION['verify_success'] ?? '';
unset($_SESSION['verify_error'], $_SESSION['verify_success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AGAP-Link | Verify Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/loginstyle.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/verify_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="verify-container">
        <div class="verify-box">

            <div class="verify-logo">AGAP-Link</div>
            <h2 class="verify-title">Verify Your Login</h2>
            <p class="verify-subtitle">
                A 6-digit verification code has been sent to<br>
                <strong><?= htmlspecialchars($maskedPhone) ?></strong>.<br>
                Enter it below to complete your sign-in.
            </p>

            <?php if ($verifyError): ?>
                <div class="verify-error"><?= htmlspecialchars($verifyError) ?></div>
            <?php endif; ?>

            <?php if ($verifySuccess): ?>
                <div class="verify-success"><?= htmlspecialchars($verifySuccess) ?></div>
            <?php endif; ?>

            <div class="countdown-wrapper">
                Code expires in <span id="countdownTimer">5:00</span>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/controller/verify_login_process.php">
                <input type="hidden" name="action" value="verify">
                <div class="otp-input-wrapper">
                    <input type="text"
                        name="token_code"
                        id="tokenCode"
                        class="otp-input"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        placeholder="······"
                        autocomplete="one-time-code"
                        required
                        autofocus>
                </div>
                <button type="submit" class="verify-btn">Verify &amp; Log In</button>
            </form>

            <form method="POST" action="<?= BASE_URL ?>/controller/verify_login_process.php" class="resend-form">
                <input type="hidden" name="action" value="resend">
                Didn't receive a code?
                <button type="submit">Resend Code</button>
            </form>

            <a href="<?= BASE_URL ?>/view/auth/index.php" class="verify-back">&#8592; Back to Login</a>

        </div>
    </div>

    <script src="<?= ASSET_URL ?>/js/login/verify_login.js"></script>
</body>

</html>