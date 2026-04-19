<?php
// 1. Define ROOT_PATH
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// 2. Load Composer and Dotenv
require_once ROOT_PATH . '/vendor/autoload.php';

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad(); // safeLoad won't throw an error if .env is missing

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. Factory function using $_ENV
function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    // We pull values from $_ENV instead of hardcoded strings
    $mail->Host       = $_ENV['MAILER_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAILER_USER'];
    $mail->Password   = $_ENV['MAILER_PASS'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = $_ENV['MAILER_PORT'];

    // OR
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    // $mail->Port       = $_ENV['MAILER_PORT'];

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'error_log';

    $mail->setFrom($_ENV['MAILER_FROM'], $_ENV['MAILER_NAME']);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    return $mail;
}
