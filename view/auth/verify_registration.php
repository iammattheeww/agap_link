<?php
require_once dirname(dirname(__DIR__)) . '/config/init.php';
session_start();

// Ensure user has pending registration (came from register form)
if (empty($_SESSION['pending_registration_email'])) {
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

$verifyError = '';
$verifySuccess = '';

if (isset($_SESSION['error'])) {
    $verifyError = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $verifySuccess = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AGAP-Link | Verify Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/loginstyle.css">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/login/verify_registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="verify-container">
        <div class="verify-box">

            <!-- KEY DIFFERENCE FROM verify_login.php: SMS instead of EMAIL -->
            <p class="verify-subtitle">
                A 6-digit verification code has been sent to<br>
                <strong><?= htmlspecialchars($_SESSION['pending_registration_phone'] ?? 'your phone') ?></strong>.<br>
                Enter it below to complete your registration.
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

            <form method="POST" action="<?= BASE_URL ?>/controller/verify_registration_process.php">
                <input type="hidden" name="action" value="verify">
                <div class="otp-input-wrapper">
                    <input type="text"
                        name="otp_code"
                        id="otpCode"
                        class="otp-input"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        placeholder="······"
                        autocomplete="off"
                        required
                        autofocus>
                </div>
                <button type="submit" class="verify-btn">Verify &amp; Complete Registration</button>
            </form>

            <form method="POST" action="<?= BASE_URL ?>/controller/verify_registration_process.php" class="resend-form">
                <input type="hidden" name="action" value="resend">
                Didn't receive a code?
                <button type="submit">Resend Code</button>
            </form>

            <a href="<?= BASE_URL ?>/view/auth/index.php" class="verify-back">&#8592; Back to Registration</a>

        </div>
    </div>

    <script src="<?= ASSET_URL ?>/js/login/verify_registration.js"></script>
</body>

</html>