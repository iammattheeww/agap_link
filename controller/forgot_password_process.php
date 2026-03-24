<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'OtpModel.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$action   = $_GET['action'] ?? '';
$user     = new User();
$otpModel = new OtpModel();

switch ($action) {

    case 'find_user':
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit();
        }

        $userData = $user->getUserByEmail($email);
        if (!$userData) {
            echo json_encode(['success' => false, 'message' => 'No account found with that email.']);
            exit();
        }

        $maskedPhone = _mask_phone_fp($userData['phone_number'] ?? '');
        $maskedEmail = _mask_email($email);

        echo json_encode([
            'success'      => true,
            'email'        => $email,
            'masked_phone' => $maskedPhone,
            'masked_email' => $maskedEmail,
        ]);
        break;

    case 'send_otp':
        $email   = trim($_POST['email']   ?? '');
        $channel = trim($_POST['channel'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($channel, ['sms', 'email'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
            exit();
        }

        $userData = $user->getUserByEmail($email);
        if (!$userData) {
            echo json_encode(['success' => false, 'message' => 'No account found with that email.']);
            exit();
        }

        $userId    = (int) $userData['user_id'];
        $firstName = $userData['first_name'];
        $phone     = $userData['phone_number'] ?? '';

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otpModel->deleteOtpsByUser($userId);
        $otpModel->insertOtp($userId, $otpCode, $channel);

        if ($channel === 'sms') {
            if (empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'No phone number on record. Please try email instead.']);
                exit();
            }
            $smsMessage = "AGAP-Link: Your password reset code is: " . $otpCode;

            try {
                require_once MODEL_PATH . 'SmsNotifier.php';
                SmsNotifier::sendRawSMS($phone, $smsMessage);
            } catch (Exception $e) {
                error_log('[forgot_password_process] SMS OTP failed: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to send SMS. Please try email instead.']);
                exit();
            }
        } else {
            try {
                require_once CONFIG_PATH . 'mailer.php';
                $mail = createMailer();
                $mail->addAddress($email, $firstName);
                $mail->Subject = 'AGAP-Link — Your Password Reset OTP';
                $mail->Body    = _build_otp_email($firstName, $otpCode);
                $mail->AltBody = "Your AGAP-Link password reset OTP is: $otpCode\nThis code expires in 5 minutes.";
                $mail->send();
                
                error_log('[forgot_password_process] Email OTP sent successfully to ' . $email);
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                error_log('[forgot_password_process] Email OTP failed: ' . $errorMsg);
                error_log('[forgot_password_process] Full exception: ' . get_class($e) . ' - ' . $e->getTraceAsString());
                
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to send email. Please try SMS instead.',
                    'debug' => $errorMsg // Remove in production
                ]);
                exit();
            }
        }

        echo json_encode(['success' => true]);
        break;

    case 'reset_password':
        $email           = trim($_POST['email']            ?? '');
        $otpCode         = trim($_POST['otp_code']         ?? '');
        $newPassword     = $_POST['new_password']          ?? '';
        $confirmPassword = $_POST['confirm_password']      ?? '';

        if (empty($email) || empty($otpCode) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit();
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
            exit();
        }

        $userData = $user->getUserByEmail($email);
        if (!$userData) {
            echo json_encode(['success' => false, 'message' => 'Account not found.']);
            exit();
        }

        $userId = (int) $userData['user_id'];

        $otpRow = $otpModel->findValidOtp($userId, $otpCode);
        if (!$otpRow) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please request a new one.']);
            exit();
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->update_password($userId, $hashed);
        $otpModel->markOtpUsed((int) $otpRow['otp_id']);

        echo json_encode(['success' => true, 'message' => 'Password reset successfully. You may now log in.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

// ── HELPERS ───────────────────────────────────────────────────────────────────

function _mask_phone_fp(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) <= 5) {
        return $phone;
    }
    $first = substr($digits, 0, 2);
    $last  = substr($digits, -3);
    $stars = str_repeat('*', strlen($digits) - 5);
    return $first . $stars . $last;
}

function _mask_email(string $email): string
{
    [$local, $domain] = explode('@', $email, 2);
    $show = max(1, (int) ceil(strlen($local) * 0.3));
    return substr($local, 0, $show) . str_repeat('*', strlen($local) - $show) . '@' . $domain;
}

function _build_otp_email(string $firstName, string $otpCode): string
{
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AGAP-Link Password Reset</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;color:#333;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 16px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.09);">

        <tr>
          <td style="background:linear-gradient(135deg,#FF6B35,#e85520);padding:38px 36px 30px;text-align:center;">
            <h1 style="margin:0 0 4px;color:#fff;font-size:1.8rem;font-weight:800;letter-spacing:1px;">AGAP-Link</h1>
            <p style="margin:0;color:rgba(255,255,255,0.85);font-size:0.9rem;">Password Reset Request</p>
          </td>
        </tr>

        <tr>
          <td style="padding:36px 36px 0;">
            <h2 style="margin:0 0 12px;color:#1a2332;font-size:1.25rem;font-weight:700;">Hello, {$firstName}!</h2>
            <p style="margin:0 0 24px;color:#4b5563;line-height:1.75;font-size:0.96rem;">
              We received a request to reset your AGAP-Link account password. Use the one-time password (OTP) below to proceed. If you did not make this request, you can safely ignore this email — your account remains secure.
            </p>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
              <tr>
                <td align="center">
                  <div style="display:inline-block;background:#fff8f5;border:2px dashed #FF6B35;border-radius:12px;padding:20px 36px;text-align:center;">
                    <p style="margin:0 0 6px;color:#6b7280;font-size:0.82rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Your OTP Code</p>
                    <p style="margin:0;color:#FF6B35;font-size:2.8rem;font-weight:800;font-family:monospace;letter-spacing:14px;line-height:1.1;">{$otpCode}</p>
                  </div>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
              <tr>
                <td style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:14px 16px;font-size:0.88rem;color:#92400e;line-height:1.65;">
                  ⏱ <strong>This code expires in 5 minutes.</strong> Do not share this code with anyone —
                  AGAP-Link staff will <strong>never</strong> ask for your OTP.
                </td>
              </tr>
            </table>

            <p style="margin:0;color:#9ca3af;font-size:0.85rem;line-height:1.6;">
              If you did not request a password reset, simply ignore this email. Your password will not be changed.
            </p>
          </td>
        </tr>

        <tr>
          <td style="padding:28px 36px 36px;text-align:center;">
            <p style="margin:0 0 4px;color:#9ca3af;font-size:0.82rem;">This is an automated message from AGAP-Link. Please do not reply.</p>
            <p style="margin:0;color:#9ca3af;font-size:0.82rem;">&copy; {$year} AGAP-Link. All rights reserved.</p>
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
