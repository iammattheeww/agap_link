<?php
require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'RegistrationVerification.php';
require_once MODEL_PATH . 'User.php';

$action = $_POST['action'] ?? '';
$regVerify = new RegistrationVerification();
$user = new User();

if ($action === 'verify') {
    // VERIFICATION FORM SUBMISSION HANDLER. THIS PROCESSES THE OTP CODE SUBMITTED BY THE USER, VALIDATES IT AGAINST THE DATABASE, AND IF VALID, CREATES THE USER ACCOUNT IN THE USERS TABLE. IT ALSO HANDLES ERROR SCENARIOS SUCH AS INVALID/EXPIRED CODES AND PROVIDES FEEDBACK TO THE USER.
    $email = $_SESSION['pending_registration_email'] ?? '';
    $otpCode = trim($_POST['otp_code'] ?? '');

    // CLEAN UP EXPIRED RECORDS FIRST
    $regVerify->deleteExpiredVerifications($email);

    // VALIDATE OTP FORMAT
    if (!preg_match('/^\d{6}$/', $otpCode)) {
        $_SESSION['error'] = 'Please enter a valid 6-digit code!';
        header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
        exit();
    }

    // FIND VERIFICATION RECORD WITH VALID OTP
    $verification = $regVerify->findValidOtp($email, $otpCode);

    if (!$verification) {
        $_SESSION['error'] = 'Invalid or expired code. Please request a new code.';
        header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
        exit();
    }

    // OTP IS VALID - CREATE USER IN DATABASE
    try {
        $user->new_user(
            $verification['temp_first_name'],
            $verification['temp_middle_initial'],
            $verification['temp_last_name'],
            $verification['temp_email'],
            $verification['temp_phone'],
            $verification['temp_password_hash']
        );

        try {
            require_once CONFIG_PATH . 'mailer.php';
            $mail = createMailer();
            $mail->addAddress(
                $verification['temp_email'],
                trim($verification['temp_first_name'] . ' ' . $verification['temp_last_name'])
            );
            $mail->Subject = 'Welcome to AGAP-Link — Your Community Reporting Platform';
            $mail->Body = _build_welcome_email($verification['temp_first_name']);
            $mail->AltBody = "Welcome to AGAP-Link, " . $verification['temp_first_name'] . "! Your account is ready. Log in at localhost/agap_link.";
            $mail->send();
        } catch (Exception $mailEx) {
            error_log('[verify_registration] Welcome email failed for ' . $verification['temp_email'] . ': ' . $mailEx->getMessage());
            // Non-blocking: don't fail registration if email fails
        }

        // MARK OTP AS USED TO PREVENT DOUBLE-REGISTRATION WITH SAME CODE
        $regVerify->markOtpUsed($verification['verification_id']);

        // DELETE TEMPORARY VERIFICATION RECORD TO CLEAN UP DATABASE AND PREVENT REUSE OF THE SAME OTP CODE. THIS ENSURES THAT ONCE A CODE HAS BEEN SUCCESSFULLY USED TO CREATE AN ACCOUNT, IT CANNOT BE USED AGAIN, EVEN IF SOMEHOW THE "used" FLAG CHECK FAILS IN THE FUTURE. THIS IS AN ADDITIONAL SAFEGUARD TO MAINTAIN THE INTEGRITY OF THE REGISTRATION PROCESS.
        $regVerify->deleteVerification($verification['verification_id']);

        // $regVerify->deleteExpiredVerifications($email);

        // CLEAR PENDING REGISTRATION SESSION VARIABLES TO PREVENT ANY RESIDUAL DATA FROM AFFECTING FUTURE REGISTRATION ATTEMPTS. THIS ENSURES THAT IF THE USER DECIDES TO REGISTER AGAIN IN THE FUTURE, THEY START WITH A CLEAN STATE WITHOUT ANY LEFTOVER DATA FROM THE PREVIOUS REGISTRATION PROCESS.
        unset($_SESSION['pending_registration_email']);
        unset($_SESSION['pending_registration_phone']);

        // SUCCESS MESSAGE
        $_SESSION['success'] = 'Account created successfully! Please log in.';
        $_SESSION['active_tab'] = 'login';
        header('Location: ' . BASE_URL . '/view/auth/index.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to create account: ' . $e->getMessage();
        error_log('[verify_registration] User creation failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
        exit();
    }
} elseif ($action === 'resend') {
    // ━━━ RESEND OTP ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    $email = $_SESSION['pending_registration_email'] ?? '';

    if (empty($email)) {
        $_SESSION['error'] = 'Session expired. Please register again.';
        header('Location: ' . BASE_URL . '/view/auth/index.php');
        exit();
    }

    // Delete old verification record
    $oldVerification = $regVerify->findValidOtp($email, '000000'); // Won't find real record, just checking

    // Get the actual verification record without OTP check
    $sql = "SELECT * FROM registration_verifications WHERE temp_email = :email AND expires_at > NOW()";
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $oldRec = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldRec) {
        $_SESSION['error'] = 'Registration session expired. Please register again.';
        header('Location: ' . BASE_URL . '/view/auth/index.php');
        exit();
    }

    // Generate new OTP and update record
    $newOtpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = (new DateTime('now', new DateTimeZone('Asia/Manila')))
        ->modify('+5 minutes')
        ->format('Y-m-d H:i:s');

    $updateSql = "UPDATE registration_verifications 
                  SET otp_code = :otp, expires_at = :expires, used = 0 
                  WHERE verification_id = :id";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([
        ':otp' => $newOtpCode,
        ':expires' => $expiresAt,
        ':id' => $oldRec['verification_id']
    ]);

    // Send new SMS
    try {
        require_once MODEL_PATH . 'SmsNotifier.php';
        $smsMessage = "AGAP-Link: Your new registration code is: " . $newOtpCode . ". Code expires in 5 minutes.";
        SmsNotifier::sendRawSMS($oldRec['temp_phone'], $smsMessage);

        $_SESSION['success'] = 'New code sent to your phone!';
        header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to resend code: ' . $e->getMessage();
        error_log('[verify_registration] Resend SMS failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/view/auth/verify_registration.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Invalid action!';
    header('Location: ' . BASE_URL . '/view/auth/index.php');
    exit();
}


// HELPER FUNCTION
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
