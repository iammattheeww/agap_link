<?php
// ─── PHPMAILER CONFIGURATION ───────────────────────────────────────────────

// 1. Define ROOT_PATH FIRST
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// 2. Load Composer Autoloader ONLY
require_once ROOT_PATH . '/vendor/autoload.php';

// 3. Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 4. Mail Config
define('MAILER_HOST', 'smtp.gmail.com');
define('MAILER_USER', 'bhorbhor2014@gmail.com');
define('MAILER_PASS', 'MatthewJustin');
define('MAILER_PORT', 587);
define('MAILER_FROM', 'bhorbhor2014@gmail.com');
define('MAILER_NAME', 'AGAP Link System');

// 5. Factory function
function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true); // true = enable exceptions

    $mail->isSMTP();
    $mail->Host       = MAILER_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAILER_USER;
    $mail->Password   = MAILER_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAILER_PORT;

    // Debug mode - captures SMTP details
    $mail->SMTPDebug = 2; // Verbose debug: 0=off, 1=errors, 2=commands+responses
    $mail->Debugoutput = 'error_log'; // Log to PHP error_log instead of stdout

    $mail->setFrom(MAILER_FROM, MAILER_NAME);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    return $mail;
}
