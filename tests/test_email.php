<?php
/**
 * PHPMailer Email Test
 * 
 * Run this from your browser at:
 * http://localhost/agap_link/tests/test_email.php
 * 
 * This will test Gmail SMTP connection and email sending
 */

require_once dirname(__DIR__) . '/config/init.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>AGAP-Link Email Test</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #ff6b35; padding-bottom: 10px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; border-radius: 4px; }
        .test.pass { border-left-color: #10b981; background: #f0fdf4; }
        .test.fail { border-left-color: #ef4444; background: #fef2f2; }
        .test.warn { border-left-color: #f59e0b; background: #fffbeb; }
        code { background: #f3f3f3; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        button { background: #ff6b35; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 10px 0; }
        button:hover { background: #e85520; }
        .log { background: #1f2937; color: #10b981; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; }
        input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; box-sizing: border-box; margin: 8px 0; }
        label { display: block; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 AGAP-Link Email Test</h1>

        <?php
        // Check 1: Composer Autoloader
        echo '<div class="test';
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            echo ' pass">';
            echo '✅ <strong>Composer</strong> - vendor/autoload.php exists';
        } else {
            echo ' fail">';
            echo '❌ <strong>Composer</strong> - vendor/autoload.php NOT FOUND';
        }
        echo '</div>';

        // Check 2: PHPMailer Classes
        echo '<div class="test';
        if (file_exists(ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
            echo ' pass">';
            echo '✅ <strong>PHPMailer</strong> - Classes found';
        } else {
            echo ' fail">';
            echo '❌ <strong>PHPMailer</strong> - NOT FOUND in vendor/';
        }
        echo '</div>';

        // Check 3: Mailer Config
        echo '<div class="test';
        if (file_exists(CONFIG_PATH . 'mailer.php')) {
            echo ' pass">';
            echo '✅ <strong>Mailer Config</strong> - config/mailer.php exists';
        } else {
            echo ' fail">';
            echo '❌ <strong>Mailer Config</strong> - config/mailer.php NOT FOUND';
        }
        echo '</div>';

        // Check 4: Environment vars
        echo '<div class="test pass">';
        echo '📋 <strong>Configuration Values:</strong><br><br>';
        echo '<strong>MAILER_HOST:</strong> ' . (defined('MAILER_HOST') ? htmlspecialchars(MAILER_HOST) : 'NOT DEFINED') . '<br>';
        echo '<strong>MAILER_PORT:</strong> ' . (defined('MAILER_PORT') ? htmlspecialchars(MAILER_PORT) : 'NOT DEFINED') . '<br>';
        echo '<strong>MAILER_USER:</strong> ' . (defined('MAILER_USER') ? htmlspecialchars(MAILER_USER) : 'NOT DEFINED') . '<br>';
        echo '<strong>MAILER_FROM:</strong> ' . (defined('MAILER_FROM') ? htmlspecialchars(MAILER_FROM) : 'NOT DEFINED') . '<br>';
        echo '<strong>MAILER_NAME:</strong> ' . (defined('MAILER_NAME') ? htmlspecialchars(MAILER_NAME) : 'NOT DEFINED') . '<br>';
        echo '</div>';
        ?>

        <h2>🧪 Send Test Email</h2>
        <form method="POST">
            <label>Recipient Email Address:</label>
            <input type="email" name="recipient" placeholder="your_email@gmail.com" required>
            
            <button type="submit" name="test_email">Send Test Email</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
            $recipient = trim($_POST['recipient'] ?? '');
            
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="test fail">❌ Invalid email address</div>';
            } else {
                echo '<div class="test">';
                echo '<strong>Testing email to: ' . htmlspecialchars($recipient) . '</strong><br><br>';
                
                try {
                    require_once CONFIG_PATH . 'mailer.php';
                    
                    $mail = createMailer();
                    $mail->addAddress($recipient);
                    $mail->Subject = 'AGAP-Link Test Email';
                    $mail->Body = '<h2>Test Email</h2><p>If you received this, PHPMailer is working correctly!</p>';
                    $mail->AltBody = 'AGAP-Link Test Email - If you received this, PHPMailer is working!';
                    
                    if ($mail->send()) {
                        echo '<div class="test pass">';
                        echo '✅ <strong>Email sent successfully!</strong><br>';
                        echo 'Check your inbox (and spam folder) for the test email.';
                        echo '</div>';
                    } else {
                        echo '<div class="test fail">';
                        echo '❌ <strong>send() returned false (no exception)</strong>';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="test fail">';
                    echo '❌ <strong>Exception thrown:</strong><br><br>';
                    echo '<div class="log">' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '<br><strong>Error Code:</strong> ' . $e->getCode() . '<br>';
                    
                    // Check if it's an SMTP error
                    if (strpos($e->getMessage(), 'SMTP') !== false) {
                        echo '<br><strong style="color:#ef4444;">⚠️ SMTP Connection Error:</strong><br>';
                        echo 'This usually means:<br>';
                        echo '• Gmail app password is incorrect or expired<br>';
                        echo '• Gmail is blocking the connection<br>';
                        echo '• Network/firewall is blocking port 587<br>';
                    }
                    
                    echo '</div>';
                    
                    error_log('[test_email.php] Email test failed: ' . $e->getMessage());
                }
            }
        }
        ?>

        <h2>🔧 Configuration Checklist</h2>
        <ol>
            <li><strong>Gmail App Password Generated?</strong>
                <ul>
                    <li>Go to: <code>https://myaccount.google.com/apppasswords</code></li>
                    <li>Select "Mail" and "Windows Computer"</li>
                    <li>Copy the 16-character app password</li>
                    <li>Paste it into <code>config/mailer.php</code> in the <code>MAILER_PASS</code> line</li>
                </ul>
            </li>
            <li><strong>2-Factor Authentication Enabled?</strong>
                <ul>
                    <li>App passwords only work if 2FA is enabled on your Gmail account</li>
                </ul>
            </li>
            <li><strong>Composer Installed?</strong>
                <ul>
                    <li>Check if <code>vendor/</code> folder exists</li>
                    <li>If not, run: <code>composer install</code> in the project root</li>
                </ul>
            </li>
            <li><strong>Firewall/Port 587 Open?</strong>
                <ul>
                    <li>Try: <code>telnet smtp.gmail.com 587</code></li>
                </ul>
            </li>
        </ol>

        <h2>📝 Logs Location</h2>
        <p>Check PHP error logs for detailed SMTP debug info:</p>
        <ul>
            <li><strong>XAMPP on Windows:</strong> <code>xampp/php/logs/</code> or <code>xampp/apache/logs/error.log</code></li>
            <li><strong>Direct command:</strong> Check System Event Viewer or PHP error_log constant location</li>
        </ul>
    </div>
</body>
</html>
