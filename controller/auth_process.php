<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'RegistrationVerification.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'OtpModel.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

switch ($action) {
  case 'register':
    register_user();
    break;

  case 'login':
    login_user();
    break;

  default:
    $_SESSION['error'] = 'Invalid action!';
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}

// THIS FUNCTION IS TO STORE THE USER'S INPUT FROM THE REGISTRATION FORM IN THE SESSION, SO THAT IF THERE'S AN ERROR DURING REGISTRATION (LIKE MISSING FIELDS OR PASSWORD MISMATCH), WE CAN PRE-FILL THE FORM WITH THE DATA THEY ALREADY ENTERED. THIS IMPROVES USER EXPERIENCE BY NOT FORCING THEM TO RE-ENTER ALL THEIR INFORMATION AFTER A VALIDATION ERROR.
function store_old_register_input(): void
{
  $_SESSION['old'] = [
    'first_name'     => $_POST['first_name']    ?? '',
    'middle_initial' => $_POST['middle_initial'] ?? '',
    'last_name'      => $_POST['last_name']      ?? '',
    'email'          => $_POST['email']          ?? '',
    'phone'          => $_POST['phone']          ?? '',
  ];
}

// function register_user(): void
// {
//   // CALLING THE USER MODEL TO HANDLE THE REGISTRATION LOGIC, INCLUDING CHECKING FOR EXISTING EMAILS AND CREATING NEW USER RECORDS IN THE DATABASE.
//   $user = new User();

//   $first_name     = trim($_POST['first_name']    ?? '');
//   $middle_initial = isset($_POST['middle_initial']) && trim($_POST['middle_initial']) !== ''
//     ? strtoupper(trim($_POST['middle_initial']))
//     : null;
//   $last_name      = trim($_POST['last_name']     ?? '');
//   $email          = trim($_POST['email']         ?? '');
//   $phone          = trim($_POST['phone']         ?? '');
//   $password       = $_POST['password']           ?? '';
//   $confirm_password = $_POST['confirm_password'] ?? '';

//   if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
//     $_SESSION['error'] = 'Please fill in all required fields!';
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }

//   if ($middle_initial !== null && strlen($middle_initial) > 5) {
//     $_SESSION['error'] = 'Middle initial should be 1 character only!';
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }

//   if ($user->email_exists($email)) {
//     $_SESSION['error'] = 'This email is already registered!';
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }

//   if (strlen($password) < 8) {
//     $_SESSION['error'] = 'Password must be at least 8 characters long!';
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }

//   if ($password !== $confirm_password) {
//     $_SESSION['error'] = 'Passwords do not match!';
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }

//   $hashed_password = password_hash($password, PASSWORD_DEFAULT);

//   try {
//     $result = $user->new_user($first_name, $middle_initial, $last_name, $email, $phone, $hashed_password);

//     if ($result) {
//       // Send welcome email — failure must NOT block registration
//       try {
//         require_once CONFIG_PATH . 'mailer.php';
//         $mail = createMailer();
//         $mail->addAddress($email, trim($first_name . ' ' . $last_name));
//         $mail->Subject = 'Welcome to AGAP-Link — Your Community Reporting Platform';
//         $mail->Body    = _build_welcome_email($first_name);
//         $mail->AltBody = "Welcome to AGAP-Link, $first_name! Your account is ready. Log in at localhost/agap_link.";
//         $mail->send();
//       } catch (Exception $mailEx) {
//         error_log('[auth_process] Welcome email failed for ' . $email . ': ' . $mailEx->getMessage());
//       }

//       $_SESSION['success'] = 'Account created successfully. Please log in.';
//       $_SESSION['active_tab'] = 'login';
//       header('Location: ' . BASE_URL . '/view/auth/index.php');
//       exit();
//     }
//   } catch (Exception $e) {
//     $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
//     $_SESSION['active_tab'] = 'register';
//     store_old_register_input();
//     header('Location: ' . BASE_URL . '/view/auth/index.php');
//     exit();
//   }
// }


function register_user(): void
{
  $user = new User();
  $regVerify = new RegistrationVerification();

  $first_name     = trim($_POST['first_name']    ?? '');
  $middle_initial = isset($_POST['middle_initial']) && trim($_POST['middle_initial']) !== ''
    ? strtoupper(trim($_POST['middle_initial']))
    : null;
  $last_name      = trim($_POST['last_name']     ?? '');
  $email          = trim($_POST['email']         ?? '');
  $phone          = trim($_POST['phone']         ?? '');
  $password       = $_POST['password']           ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    $_SESSION['error'] = 'Please fill in all required fields!';
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  if ($middle_initial !== null && strlen($middle_initial) > 5) {
    $_SESSION['error'] = 'Middle initial should be 1 character only!';
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  // CHECKS IF EMAIL ALREADY EXISTS IN BOTH USERS TABLE AND PENDING VERIFICATIONS
  if ($user->email_exists($email) || $regVerify->hasPendingVerification($email)) {
    $_SESSION['error'] = 'This email is already registered or pending verification!';
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters long!';
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match!';
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  // GENERATE OTP & STORE TEMPORARY DATA IN registration_verifications TABLE in .sql FILE.
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

  try {
    // STORE EVERYTHING IN registration_verifications TABLE
    $result = $regVerify->createVerification(
      $first_name,
      $middle_initial,
      $last_name,
      $email,
      $phone,
      $hashed_password,
      $otpCode
    );

    if (!$result) {
      throw new Exception('Failed to create verification record');
    }

    // SEND OTP VIA SMS — FAILURE MUST NOT BLOCK REGISTRATION. THIS IS TO ENSURE THAT EVEN IF THE SMS SERVICE FACES ISSUES, THE USER CAN STILL COMPLETE REGISTRATION USING THE OTP CODE SENT TO THEIR EMAIL. THIS DESIGN CHOICE PRIORITIZES USER EXPERIENCE AND ACCOUNT CREATION SUCCESS WHILE STILL ATTEMPTING TO PROVIDE THE CONVENIENCE OF SMS VERIFICATION.
    try {
      require_once MODEL_PATH . 'SmsNotifier.php';
      $smsMessage = "AGAP-Link: Your registration code is: " . $otpCode . ". Code expires in 5 minutes.";
      SmsNotifier::sendRawSMS($phone, $smsMessage);
    } catch (Exception $smsEx) {
      error_log('[auth_process] Registration OTP SMS failed: ' . $smsEx->getMessage());
      throw new Exception('Failed to send SMS. Please try again.');
    }

    // STORE IN SESSION FOR VERIFICATION PAGE — THIS IS TO PASS THE USER'S EMAIL AND MASKED PHONE NUMBER TO THE VERIFICATION PAGE, SO THAT THE PAGE CAN DISPLAY THIS INFORMATION TO THE USER (E.G., "
    $_SESSION['pending_registration_email'] = $email;
    $_SESSION['pending_registration_phone'] = _mask_phone($phone);

    header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
    exit();

  } catch (Exception $e) {
    $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
    $_SESSION['active_tab'] = 'register';
    store_old_register_input();
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }
}

function login_user(): void
{
  // $user     = new User();
  // $otpModel = new OtpModel();

  // $email    = trim($_POST['email']    ?? '');
  // $password = trim($_POST['password'] ?? '');

  // if (empty($email) || empty($password)) {
  //     $_SESSION['error'] = 'Please fill in all fields!';
  //     header('Location: ' . BASE_URL . '/view/auth/index.php');
  //     exit();
  // }

  // // Admin login — no MFA
  // $admin_data = $user->admin_check_login($email, $password);
  // if ($admin_data) {
  //     $_SESSION['admin_logged_in'] = true;
  //     $_SESSION['admin_id']        = $admin_data['id'];
  //     $_SESSION['admin_email']     = $admin_data['email'];
  //     $_SESSION['admin_name']      = $admin_data['name'];
  //     header('Location: ' . BASE_URL . '/view/admin_module/admin_dashboard.php');
  //     exit();
  // }

  // // Regular user login — SMS MFA
  // $user_data = $user->check_login($email, $password);

  // if ($user_data) {
  //     $userId = (int) $user_data['user_id'];
  //     $phone  = $user_data['phone_number'] ?? '';

  //     $tokenCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

  //     $otpModel->deleteTokensByUser($userId);
  //     $otpModel->insertToken($userId, $tokenCode);

  //     $smsMessage = "AGAP-Link: Your login code is: " . $tokenCode;

  //     if (!empty($phone)) {
  //         try {
  //             require_once MODEL_PATH . 'SmsNotifier.php';
  //             SmsNotifier::sendRawSMS($phone, $smsMessage);
  //         } catch (Exception $e) {
  //             error_log('[auth_process] Login token SMS failed: ' . $e->getMessage());
  //         }
  //     }

  //     $_SESSION['pending_login_user_id']      = $userId;
  //     $_SESSION['pending_login_role']         = 'user';
  //     $_SESSION['pending_login_masked_phone'] = _mask_phone($phone);

  //     header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
  //     exit();
  // }

  // $_SESSION['error'] = 'Invalid email or password!';
  // $_SESSION['active_tab'] = 'login';
  // header('Location: ' . BASE_URL . '/view/auth/index.php');
  // exit();

  $user     = new User();
  $otpModel = new OtpModel();

  $email    = trim($_POST['email']    ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields!';
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
  }

  // Admin login — no MFA
  $admin_data = $user->admin_check_login($email, $password);
  if ($admin_data) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id']        = $admin_data['id'];
    $_SESSION['admin_email']     = $admin_data['email'];
    $_SESSION['admin_name']      = $admin_data['name'];

    // UPDATE THE LAST LOGIN TIMESTAMP FOR ADMIN USERS
    $user->update_admin_last_login($admin_data['id']);

    header('Location: ' . BASE_URL . '/view/admin_module/admin_dashboard.php');
    exit();
  }

  // Agency user login — no MFA (based on your current implementation)
  $agency_data = $user->agency_check_login($email, $password);
  if ($agency_data) {
    $_SESSION['agency_logged_in'] = true;
    $_SESSION['agency_user_id']   = $agency_data['agency_user_id'];
    $_SESSION['agency_id']        = $agency_data['agency_id'];
    $_SESSION['agency_name']      = $agency_data['agency_name'];
    $_SESSION['agency_full_name'] = $agency_data['full_name'];

    // UPDATE THE LAST LOGIN TIMESTAMP FOR AGENCY USERS
    $user->update_agency_last_login($agency_data['agency_user_id']);

    header('Location: ' . BASE_URL . '/view/lgu_module/agency_dashboard.php');
    exit();
  }

  // Regular user login — SMS MFA
  $user_data = $user->check_login($email, $password);

  if ($user_data) {
    $userId = (int) $user_data['user_id'];
    $phone  = $user_data['phone_number'] ?? '';

    $tokenCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $otpModel->deleteTokensByUser($userId);
    $otpModel->insertToken($userId, $tokenCode);

    $smsMessage = "AGAP-Link: Your login code is: " . $tokenCode;

    // if (!empty($phone)) {
    //   try {
    //     require_once MODEL_PATH . 'SmsNotifier.php';
    //     SmsNotifier::sendRawSMS($phone, $smsMessage);
    //   } catch (Exception $e) {
    //     error_log('[auth_process] Login token SMS failed: ' . $e->getMessage());
    //   }
    // }

    try {
      require_once CONFIG_PATH . 'mailer.php';
      $mail = createMailer();
      $userEmail = $user_data['email'] ?? '';
      if (!empty($userEmail)) {
        $mail->addAddress($userEmail, trim($user_data['first_name'] . ' ' . $user_data['last_name']));
        $mail->Subject = 'Your AGAP-Link Login Verification Code';
        // $mail->Body = _build_login_verification_email($userData['first_name'], $tokenCode);
        $mail->Body = _build_login_verification_email($user_data['first_name'], $tokenCode);
        $mail->AltBody = "Your AGAP-Link login verification code is: " . $tokenCode . ". This code expires in 5 minutes.";
        $mail->send();
      }
    } catch (Exception $e) {
      error_log('[auth_process] Login verification email failed: ' . $e->getMessage());
    }

    // UPDATE THE LAST LOGIN TIMESTAMP FOR REGULAR USERS OR RESIDENTS
    $user->update_user_last_login($userId);

    $_SESSION['pending_login_user_id']      = $userId;
    $_SESSION['pending_login_role']         = 'user';
    $_SESSION['pending_login_masked_phone'] = _mask_phone($phone);

    header('Location: ' . BASE_URL . '/view/auth/verify_login.php');
    exit();
  }

  $_SESSION['error'] = 'Invalid email or password!';
  $_SESSION['active_tab'] = 'login';
  header('Location: ' . BASE_URL . '/view/auth/index.php');
  exit();
}

// ── HELPER FUNCTIONS ───────────────────────────────────────────────────────────────────

function _mask_phone(string $phone): string
{
  $digits = preg_replace('/\D/', '', $phone);
  if (strlen($digits) <= 6) {
    return $phone;
  }
  $first = substr($digits, 0, 3);
  $last  = substr($digits, -3);
  $stars = str_repeat('*', strlen($digits) - 6);
  return $first . $stars . $last;
}

// BUILD WELCOME EMAIL TEMPLATE (HTML AND INLINE CSS ONLY)
function _build_welcome_email(string $firstName): string
{
  $year         = date('Y');
  $dashboardUrl = BASE_URL . '/view/user_module/user_dashboard.php';

  // DEBUGGING OUTPUT TO VERIFY BASE_URL AND DASHBOARD URL VALUES — IN LAYMAN'S TERM, THIS IS TO CHECK IF BASE_URL IS CORRECTLY SET AND IF THE DASHBOARD URL IS BEING CONSTRUCTED PROPERLY. THIS CAN HELP IDENTIFY ISSUES WITH URL FORMATION IN THE EMAIL TEMPLATE.
  error_log('[DEBUG] BASE_URL value: ' . var_export(BASE_URL, true));
  error_log('[DEBUG] $dashboardUrl: ' . $dashboardUrl);
  error_log('[DEBUG] HTTP_HOST: ' . $_SERVER['HTTP_HOST']);

  return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to AGAP-Link</title>
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
            <p style="margin:0;color:rgba(255,255,255,0.85);font-size:0.95rem;font-weight:400;">Community Reporting Platform</p>
          </td>
        </tr>

        <!-- GREETING -->
        <tr>
          <td style="padding:40px 36px 0;">
            <h2 style="margin:0 0 14px;color:#1a2332;font-size:1.55rem;font-weight:700;">Welcome aboard, {$firstName}! 🎉</h2>
            <p style="margin:0;color:#4b5563;line-height:1.75;font-size:0.97rem;">
              We're thrilled to have you join the <strong style="color:#FF6B35;">AGAP-Link</strong> community.
              Your account is now active and you can start making a real difference in your community right away.
              This platform was built for residents like you — so your voice is heard, and your concerns are acted upon.
            </p>
          </td>
        </tr>

        <!-- WHAT IS AGAP-LINK -->
        <tr>
          <td style="padding:28px 36px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8f5;border-left:4px solid #FF6B35;border-radius:0 10px 10px 0;">
              <tr>
                <td style="padding:22px 22px;">
                  <h3 style="margin:0 0 10px;color:#FF6B35;font-size:1.05rem;font-weight:700;">What is AGAP-Link?</h3>
                  <p style="margin:0 0 12px;color:#4b5563;font-size:0.93rem;line-height:1.72;">
                    AGAP-Link is a community-driven incident reporting platform built for <strong style="color:#1a2332;">Bacolod City</strong>.
                    It bridges the gap between citizens and local government agencies, making it fast and easy to report
                    concerns directly to the people who can fix them.
                  </p>
                  <p style="margin:0 0 8px;color:#4b5563;font-size:0.93rem;font-weight:600;">You can report concerns about:</p>
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:2px 16px 2px 0;font-size:0.9rem;color:#4b5563;">🏗️ Infrastructure &amp; Road Damage</td>
                      <td style="padding:2px 0;font-size:0.9rem;color:#4b5563;">🚨 Public Safety Concerns</td>
                    </tr>
                    <tr>
                      <td style="padding:2px 16px 2px 0;font-size:0.9rem;color:#4b5563;">🗑️ Waste Management Issues</td>
                      <td style="padding:2px 0;font-size:0.9rem;color:#4b5563;">🌿 Environmental Hazards</td>
                    </tr>
                    <tr>
                      <td style="padding:2px 16px 2px 0;font-size:0.9rem;color:#4b5563;">💡 Utilities &amp; Power Outages</td>
                      <td style="padding:2px 0;font-size:0.9rem;color:#4b5563;">🚦 Traffic &amp; Road Conditions</td>
                    </tr>
                    <tr>
                      <td style="padding:2px 16px 2px 0;font-size:0.9rem;color:#4b5563;">🏥 Public Health Concerns</td>
                      <td style="padding:2px 0;font-size:0.9rem;color:#4b5563;">🏫 Community Facility Requests</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- WHAT YOU CAN DO -->
        <tr>
          <td style="padding:28px 36px 0;">
            <h3 style="margin:0 0 18px;color:#1a2332;font-size:1.05rem;font-weight:700;">What Can You Do with AGAP-Link?</h3>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td width="44" valign="top">
                  <div style="width:38px;height:38px;background:#FF6B35;border-radius:50%;text-align:center;line-height:38px;font-size:1.1rem;">📸</div>
                </td>
                <td style="padding-left:14px;vertical-align:top;">
                  <strong style="color:#1a2332;display:block;margin-bottom:3px;">Submit Reports with Photos &amp; GPS</strong>
                  <span style="color:#6b7280;font-size:0.9rem;line-height:1.6;">Attach photos and let GPS pinpoint the exact location of the issue — no guessing, just precise, verifiable reports.</span>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td width="44" valign="top">
                  <div style="width:38px;height:38px;background:#FF6B35;border-radius:50%;text-align:center;line-height:38px;font-size:1.1rem;">📊</div>
                </td>
                <td style="padding-left:14px;vertical-align:top;">
                  <strong style="color:#1a2332;display:block;margin-bottom:3px;">Track Status in Real-Time</strong>
                  <span style="color:#6b7280;font-size:0.9rem;line-height:1.6;">Watch your report move through every stage: <em>Pending → Verified → Forwarded → Ongoing → Resolved</em>.</span>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td width="44" valign="top">
                  <div style="width:38px;height:38px;background:#FF6B35;border-radius:50%;text-align:center;line-height:38px;font-size:1.1rem;">📱</div>
                </td>
                <td style="padding-left:14px;vertical-align:top;">
                  <strong style="color:#1a2332;display:block;margin-bottom:3px;">Receive SMS Updates Automatically</strong>
                  <span style="color:#6b7280;font-size:0.9rem;line-height:1.6;">Get a text message every time your report status changes. You're always in the loop — even when you're not online.</span>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td width="44" valign="top">
                  <div style="width:38px;height:38px;background:#FF6B35;border-radius:50%;text-align:center;line-height:38px;font-size:1.1rem;">📢</div>
                </td>
                <td style="padding-left:14px;vertical-align:top;">
                  <strong style="color:#1a2332;display:block;margin-bottom:3px;">View Official Community Announcements</strong>
                  <span style="color:#6b7280;font-size:0.9rem;line-height:1.6;">Stay informed with important updates, advisories, and news published directly by AGAP-Link administrators.</span>
                </td>
              </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="44" valign="top">
                  <div style="width:38px;height:38px;background:#FF6B35;border-radius:50%;text-align:center;line-height:38px;font-size:1.1rem;">🤝</div>
                </td>
                <td style="padding-left:14px;vertical-align:top;">
                  <strong style="color:#1a2332;display:block;margin-bottom:3px;">Contribute to Building a Better Bacolod City</strong>
                  <span style="color:#6b7280;font-size:0.9rem;line-height:1.6;">Every report you submit helps build a safer, cleaner, and more responsive city. Civic engagement starts with you.</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- HOW IT WORKS -->
        <tr>
          <td style="padding:28px 36px 0;">
            <h3 style="margin:0 0 18px;color:#1a2332;font-size:1.05rem;font-weight:700;">How It Works — 3 Simple Steps</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="33%" valign="top" style="text-align:center;padding:0 8px;">
                  <div style="width:48px;height:48px;background:#FF6B35;color:#fff;border-radius:50%;font-size:1.3rem;font-weight:800;line-height:48px;margin:0 auto 10px;">1</div>
                  <strong style="color:#1a2332;display:block;margin-bottom:5px;font-size:0.92rem;">Register &amp; Log In</strong>
                  <span style="color:#6b7280;font-size:0.85rem;line-height:1.6;">Create your account and sign in securely with SMS verification.</span>
                </td>
                <td width="33%" valign="top" style="text-align:center;padding:0 8px;">
                  <div style="width:48px;height:48px;background:#FF6B35;color:#fff;border-radius:50%;font-size:1.3rem;font-weight:800;line-height:48px;margin:0 auto 10px;">2</div>
                  <strong style="color:#1a2332;display:block;margin-bottom:5px;font-size:0.92rem;">Submit a Report</strong>
                  <span style="color:#6b7280;font-size:0.85rem;line-height:1.6;">Describe the issue, add a photo, and let GPS auto-fill the location.</span>
                </td>
                <td width="33%" valign="top" style="text-align:center;padding:0 8px;">
                  <div style="width:48px;height:48px;background:#FF6B35;color:#fff;border-radius:50%;font-size:1.3rem;font-weight:800;line-height:48px;margin:0 auto 10px;">3</div>
                  <strong style="color:#1a2332;display:block;margin-bottom:5px;font-size:0.92rem;">Track &amp; Get Updates</strong>
                  <span style="color:#6b7280;font-size:0.85rem;line-height:1.6;">Monitor your report status live and get SMS alerts at every step.</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- CTA BUTTON -->
        <tr>
          <td style="padding:36px 36px 0;text-align:center;">
            <a href="{$dashboardUrl}"
               style="display:inline-block;background:linear-gradient(135deg,#FF6B35,#e85520);color:#ffffff;text-decoration:none;font-size:1rem;font-weight:700;padding:16px 40px;border-radius:50px;letter-spacing:0.5px;box-shadow:0 4px 16px rgba(255,107,53,0.4);">
              Go to AGAP-Link &rarr;
            </a>
          </td>
        </tr>

        <!-- PRIVACY NOTE -->
        <tr>
          <td style="padding:28px 36px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:10px;">
              <tr>
                <td style="padding:16px 20px;color:#6b7280;font-size:0.85rem;line-height:1.7;">
                  🔒 <strong style="color:#374151;">Your Privacy Matters.</strong>
                  The personal information you provided during registration is used solely for account management, report communication, and SMS verification. AGAP-Link will never sell or share your data with third parties without your consent.
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="padding:36px 36px 40px;text-align:center;">
            <p style="margin:0 0 6px;color:#9ca3af;font-size:0.82rem;">This email was sent because you registered an account on AGAP-Link.</p>
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

// BUILD LOGIN VERIFICATION EMAIL TEMPLATE (HTML AND INLINE CSS ONLY)
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
