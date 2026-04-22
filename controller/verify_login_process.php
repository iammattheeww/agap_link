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

        $_SESSION['verify_success'] = 'A new verification code has been sent to your phone.';
        header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
        exit();

    default:
        header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
        exit();
}
