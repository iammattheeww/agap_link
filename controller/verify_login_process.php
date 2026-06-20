<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'OtpModel.php';
require_once MODEL_PATH . 'SmsNotifier.php';

// Session guard
if (!isset($_SESSION['pending_login_user_id'])) {
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
    exit();
}

$userId    = (int) $_SESSION['pending_login_user_id'];
$action    = $_POST['action'] ?? '';
$userModel = new User();
$otpModel  = new OtpModel();

switch ($action) {

    case 'verify':
        $tokenCode = trim($_POST['token_code'] ?? '');

        if (strlen($tokenCode) !== 6 || !ctype_digit($tokenCode)) {
            $_SESSION['verify_error'] = 'Please enter a valid 6-digit code.';
            header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
            exit();
        }

        $tokenRow = $otpModel->findValidToken($userId, $tokenCode);

        if (!$tokenRow) {
            $_SESSION['verify_error'] = 'Invalid or expired code. Please request a new one.';
            header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
            exit();
        }

        $otpModel->markTokenUsed((int) $tokenRow['token_id']);

        $userData = $userModel->get_user_details($userId);

        if (!$userData) {
            unset(
                $_SESSION['pending_login_user_id'],
                $_SESSION['pending_login_role'],
                $_SESSION['pending_login_masked_phone']
            );
            $_SESSION['error'] = 'Account not found. Please log in again.';
            header('Location: ' . BASE_URL . '/view/auth/index.php');
            exit();
        }

        unset(
            $_SESSION['pending_login_user_id'],
            $_SESSION['pending_login_role'],
            $_SESSION['pending_login_masked_phone']
        );

        $middlePart = !empty($userData['middle_initial'])
            ? $userData['middle_initial'] . '. '
            : '';
        $fullName = trim($userData['first_name'] . ' ' . $middlePart . $userData['last_name']);

        $_SESSION['user_logged_in']      = true;
        $_SESSION['user_id']             = $userData['user_id'];
        $_SESSION['user_first_name']     = $userData['first_name'];
        $_SESSION['user_middle_initial'] = $userData['middle_initial'];
        $_SESSION['user_last_name']      = $userData['last_name'];
        $_SESSION['user_name']           = $fullName;
        $_SESSION['user_email']          = $userData['email'];
        $_SESSION['user_phone']          = $userData['phone_number'];

        header('Location: ' . BASE_URL . '/view/landing_module/index.php');
        exit();

    case 'resend':
        $userData = $userModel->get_user_details($userId);

        if (!$userData) {
            unset(
                $_SESSION['pending_login_user_id'],
                $_SESSION['pending_login_role'],
                $_SESSION['pending_login_masked_phone']
            );
            $_SESSION['error'] = 'Account not found. Please log in again.';
            header('Location: ' . BASE_URL . '/view/auth/index.php');
            exit();
        }

        $phone     = $userData['phone_number'] ?? '';
        $tokenCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otpModel->deleteTokensByUser($userId);
        $otpModel->insertToken($userId, $tokenCode);

        // $smsMessage =
        //     "AGAP-Link — Login Verification\n\n" .
        //     "Your new temporary login verification code is:\n\n" .
        //     $tokenCode . "\n\n" .
        //     "This code is valid for 5 minutes only.\n\n" .
        //     "– AGAP-Link Security";

        // if (!empty($phone)) {
        //     try {
        //         SmsNotifier::sendRawSMS($phone, $smsMessage);
        //     } catch (Exception $e) {
        //         error_log('[verify_login_process] Resend SMS failed: ' . $e->getMessage());
        //     }
        // }

        try {
            require_once CONFIG_PATH . 'mailer.php';
            $mail = createMailer();
            $userEmail = $userData['email'] ?? '';
            if (!empty($userEmail)) {
                $mail->addAddress($userEmail, trim($userData['first_name'] . ' ' . $userData['last_name']));
                $mail->Subject = 'Your New AGAP-Link Login Verification Code';
                $mail->Body = _build_login_verification_email($userData['first_name'], $tokenCode);
                $mail->AltBody = "Your new AGAP-Link login verification code is: " . $tokenCode . ". This code expires in 5 minutes.";
                $mail->send();
            }
        } catch (Exception $e) {
            error_log('[verify_login_process] Resend email failed: ' . $e->getMessage());
        }

        $_SESSION['verify_success'] = 'A new verification code has been sent to your email';
        header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
        exit();

    default:
        header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
        exit();
}
function _build_login_verification_email(string $firstName, string $tokenCode): string
{
  return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Verification - AGAP-Link</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.09);">

        <!-- HEADER -->
        <tr>
          <td style="background:linear-gradient(135deg,#FF6B35 0%,#e85520 100%);padding:44px 36px 36px;text-align:center;">
            <h1 style="margin:0 0 6px;color:#ffffff;font-size:2rem;font-weight:800;letter-spacing:1.5px;line-height:1.2;">AGAP-Link</h1>
            <p style="margin:0;color:rgba(255,255,255,0.85);font-size:0.95rem;font-weight:400;">Secure Login Verification</p>
          </td>
        </tr>

        <!-- CONTENT -->
        <tr>
          <td style="padding:40px 36px;">
            <h2 style="margin:0 0 14px;color:#1a2332;font-size:1.4rem;font-weight:700;">Hi {$firstName}! 🔐</h2>
            <p style="margin:0 0 20px;color:#4b5563;line-height:1.75;font-size:0.97rem;">
              We received a login request for your AGAP-Link account. Use the verification code below to complete your login.
            </p>

            <!-- OTP CODE BOX -->
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3f0;border:2px solid #FF6B35;border-radius:10px;margin:28px 0;">
              <tr>
                <td style="padding:28px;text-align:center;">
                  <p style="margin:0 0 12px;color:#666;font-size:0.9rem;text-transform:uppercase;letter-spacing:2px;">Your Verification Code</p>
                  <p style="margin:0;color:#FF6B35;font-size:3rem;font-weight:800;letter-spacing:8px;font-family:monospace;">{$tokenCode}</p>
                  <p style="margin:10px 0 0;color:#999;font-size:0.85rem;">This code expires in <strong>5 minutes</strong></p>
                </td>
              </tr>
            </table>

            <p style="margin:20px 0;color:#4b5563;line-height:1.75;font-size:0.95rem;">
              <strong>⏱️ Valid for 5 minutes only:</strong> If you don't complete your login within 5 minutes, the code will expire and you'll need to request a new one.
            </p>

            <p style="margin:20px 0;color:#4b5563;line-height:1.75;font-size:0.95rem;">
              <strong>🔒 Didn't request this?</strong> If you didn't attempt to log in, ignore this email or change your password immediately.
            </p>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="padding:28px 36px 40px;border-top:1px solid #eee;text-align:center;">
            <p style="margin:0 0 8px;color:#999;font-size:0.8rem;">This is an automated message from AGAP-Link Security.</p>
            <p style="margin:0;color:#999;font-size:0.8rem;">&copy; 2026 AGAP-Link. All rights reserved.</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
HTML;
}