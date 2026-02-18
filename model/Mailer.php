<?php
/**
 * Mailer.php — PHPMailer email notification helper for AGAP-Link.
 *
 * SETUP INSTRUCTIONS:
 * 1. Download PHPMailer from https://github.com/PHPMailer/PHPMailer/releases
 * 2. Extract and place PHPMailer files in: agap_link/assets/lib/PHPMailer/
 *    Required files: PHPMailer.php, SMTP.php, Exception.php
 * 3. Fill in SMTP credentials in config/mailer_config.php
 */

require_once dirname(__DIR__) . '/config/init.php';
require_once dirname(__DIR__) . '/config/mailer_config.php';

// PHPMailer autoload — gracefully skip if library not installed
$phpmailerPath = dirname(__DIR__) . '/assets/lib/PHPMailer/PHPMailer.php';
if (!file_exists($phpmailerPath)) {
    function sendStatusUpdateEmail($conn, $report_id, $new_status, $remarks = '') {
        throw new Exception('PHPMailer library not installed. Place PHPMailer files in assets/lib/PHPMailer/.');
    }
    return;
}

require_once $phpmailerPath;
require_once dirname(__DIR__) . '/assets/lib/PHPMailer/SMTP.php';
require_once dirname(__DIR__) . '/assets/lib/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * Send a status-update email to the report's submitter.
 *
 * @param PDO    $conn       Active database connection
 * @param int    $report_id  ID of the report that was updated
 * @param string $new_status The new status value
 * @param string $remarks    Optional admin remarks
 * @throws Exception         If email fails to send
 */
function sendStatusUpdateEmail(PDO $conn, int $report_id, string $new_status, string $remarks = ''): void
{
    // Fetch report + reporter email in one query
    $stmt = $conn->prepare("
        SELECT r.report_id, r.description, r.address, r.status,
               c.name AS category_name,
               u.email AS user_email,
               CONCAT(u.first_name, ' ', IFNULL(CONCAT(u.middle_initial, '. '), ''), u.last_name) AS user_name
        FROM reports r
        LEFT JOIN categories c ON r.category_id = c.category_id
        LEFT JOIN users u ON r.user_id = u.user_id
        WHERE r.report_id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report || empty($report['user_email'])) {
        throw new Exception("Could not find user email for report #$report_id");
    }

    $dashboardUrl = BASE_URL . '/view/user_module/my_reports.php';
    $statusColor  = match ($new_status) {
        'Resolved'  => '#10b981',
        'Verified'  => '#3b82f6',
        'Forwarded' => '#6366f1',
        'Ongoing'   => '#f59e0b',
        default     => '#6b7280',
    };

    $remarksHtml = !empty($remarks)
        ? "<p style='margin:0 0 12px;'><strong>Admin Remarks:</strong> " . htmlspecialchars($remarks) . "</p>"
        : '';

    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family:Outfit,Arial,sans-serif; background:#f8f9fa; margin:0; padding:0;'>
      <div style='max-width:560px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>
        <div style='background:#ff6b35; padding:28px 32px;'>
          <h1 style='color:#fff; margin:0; font-size:1.4rem; font-family:Sora,Arial,sans-serif;'>AGAP-Link</h1>
          <p style='color:rgba(255,255,255,0.85); margin:6px 0 0; font-size:0.9rem;'>Community Issue Reporting Platform</p>
        </div>
        <div style='padding:32px;'>
          <h2 style='color:#2c3e50; margin:0 0 16px; font-family:Sora,Arial,sans-serif; font-size:1.2rem;'>
            Your Report Has Been Updated
          </h2>
          <p style='color:#6b7280; margin:0 0 24px;'>
            Hello, <strong style='color:#2c3e50;'>" . htmlspecialchars($report['user_name']) . "</strong>!
            Your report status has been updated.
          </p>

          <div style='background:#f5f7fa; border-radius:10px; padding:20px; margin-bottom:24px;'>
            <p style='margin:0 0 10px; color:#374151;'><strong>Report #" . $report_id . "</strong></p>
            <p style='margin:0 0 6px; color:#6b7280; font-size:0.9rem;'>Category: <strong style='color:#374151;'>" . htmlspecialchars($report['category_name'] ?? 'General') . "</strong></p>
            <p style='margin:0 0 6px; color:#6b7280; font-size:0.9rem;'>Address: " . htmlspecialchars($report['address']) . "</p>
          </div>

          <div style='text-align:center; margin-bottom:24px;'>
            <p style='color:#6b7280; margin:0 0 8px; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.06em;'>New Status</p>
            <span style='display:inline-block; background:" . $statusColor . "; color:#fff; padding:10px 28px; border-radius:30px; font-weight:700; font-size:1rem; letter-spacing:0.04em;'>
              " . htmlspecialchars($new_status) . "
            </span>
          </div>

          $remarksHtml

          <div style='text-align:center; margin-top:28px;'>
            <a href='" . $dashboardUrl . "'
               style='display:inline-block; background:#ff6b35; color:#fff; text-decoration:none;
                      padding:12px 28px; border-radius:8px; font-weight:600; font-size:0.95rem;'>
              View My Reports →
            </a>
          </div>
        </div>
        <div style='background:#f5f7fa; padding:16px 32px; text-align:center;'>
          <p style='color:#9ca3af; font-size:0.8rem; margin:0;'>
            &copy; 2026 AGAP-Link. Bacolod City, Philippines.
          </p>
        </div>
      </div>
    </body>
    </html>";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port       = MAIL_PORT;

    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($report['user_email'], $report['user_name']);

    $mail->isHTML(true);
    $mail->Subject = "[AGAP-Link] Your Report #$report_id Status Has Been Updated";
    $mail->Body    = $htmlBody;
    $mail->AltBody = "Hello {$report['user_name']}, your AGAP-Link report #$report_id has been updated to: $new_status. $remarks";

    $mail->send();
}
