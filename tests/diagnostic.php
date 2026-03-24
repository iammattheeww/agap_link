<?php
/**
 * AGAP-Link SMS/Email Diagnostic Test
 * 
 * Run this from your browser at:
 * http://localhost/agap_link/tests/diagnostic.php
 * 
 * This will check:
 * 1. Phone numbers in database
 * 2. SMS API configuration
 * 3. Email configuration
 * 4. Try to send test SMS
 */

require_once dirname(__DIR__) . '/config/init.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'SmsNotifier.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>AGAP-Link Diagnostic</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 2px solid #ff6b35; padding-bottom: 10px; }
        .check { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; border-radius: 4px; }
        .check.pass { border-left-color: #10b981; background: #f0fdf4; }
        .check.fail { border-left-color: #ef4444; background: #fef2f2; }
        .check.warn { border-left-color: #f59e0b; background: #fffbeb; }
        code { background: #f3f3f3; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9fafb; font-weight: bold; }
        .status { font-weight: bold; }
        .status.ok { color: #10b981; }
        .status.error { color: #ef4444; }
        .status.warn { color: #f59e0b; }
        button { background: #ff6b35; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 10px 0; }
        button:hover { background: #e85520; }
        .log { background: #1f2937; color: #10b981; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 AGAP-Link SMS/Email Diagnostic</h1>

        <!-- 1. DATABASE PHONE NUMBERS -->
        <h2>1. Database Phone Numbers Check</h2>
        <?php
        try {
            $userModel = new User();
            $phones = $userModel->getAllPhoneNumbers();
            
            if (empty($phones)) {
                echo '<div class="check fail"><strong>❌ ERROR:</strong> No phone numbers found in database</div>';
            } else {
                echo '<div class="check pass"><strong>✅ Found ' . count($phones) . ' phone number(s)</strong></div>';
                echo '<table>';
                echo '<tr><th>#</th><th>Phone Number</th><th>Format</th><th>Status</th></tr>';
                foreach (array_slice($phones, 0, 5) as $idx => $phone) {
                    $phone_clean = preg_replace('/\D/', '', $phone);
                    $format = 'Unknown';
                    $status = '<span class="status error">Invalid</span>';
                    
                    if (preg_match('/^09\d{9}$/', $phone_clean)) {
                        $format = '09XXXXXXXXX (Local)';
                        $status = '<span class="status ok">Valid - Will convert to 639xxx</span>';
                    } elseif (preg_match('/^639\d{9}$/', $phone_clean)) {
                        $format = '639XXXXXXXXX (International)';
                        $status = '<span class="status ok">Valid - Ready to send</span>';
                    }
                    
                    echo '<tr><td>' . ($idx + 1) . '</td><td><code>' . htmlspecialchars($phone) . '</code></td><td>' . $format . '</td><td>' . $status . '</td></tr>';
                }
                echo '</table>';
                if (count($phones) > 5) {
                    echo '<p><em>... and ' . (count($phones) - 5) . ' more phone numbers</em></p>';
                }
            }
        } catch (Exception $e) {
            echo '<div class="check fail"><strong>❌ Database Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

        <!-- 2. SMS CONFIGURATION -->
        <h2>2. SMS Configuration Check</h2>
        <?php
        $checks = [
            'PHILSMS_API_KEY' => defined('PHILSMS_API_KEY') && !empty(PHILSMS_API_KEY),
            'PHILSMS_API_URL' => defined('PHILSMS_API_URL') && !empty(PHILSMS_API_URL),
            'PHILSMS_SENDER_ID' => defined('PHILSMS_SENDER_ID') && !empty(PHILSMS_SENDER_ID),
        ];

        foreach ($checks as $const => $result) {
            $value = defined($const) ? constant($const) : 'NOT DEFINED';
            if ($const === 'PHILSMS_API_KEY' && strlen($value) > 20) {
                $value = substr($value, 0, 20) . '...';
            }
            $class = $result ? 'pass' : 'fail';
            $icon = $result ? '✅' : '❌';
            echo '<div class="check ' . $class . '"><strong>' . $icon . ' ' . $const . ':</strong> <code>' . htmlspecialchars($value) . '</code></div>';
        }
        ?>

        <!-- 3. EMAIL CONFIGURATION -->
        <h2>3. Email (PHPMailer) Configuration Check</h2>
        <?php
        $composer_exists = file_exists(ROOT_PATH . '/vendor/autoload.php');
        $mailer_config = file_exists(CONFIG_PATH . 'mailer.php');
        
        echo '<div class="check ' . ($composer_exists ? 'pass' : 'fail') . '"><strong>' . ($composer_exists ? '✅' : '❌') . ' Composer:</strong> ' . (file_exists(ROOT_PATH . '/vendor') ? 'vendor/ folder exists' : 'vendor/ NOT FOUND') . '</div>';
        echo '<div class="check ' . ($mailer_config ? 'pass' : 'fail') . '"><strong>' . ($mailer_config ? '✅' : '❌') . ' Mailer Config:</strong> config/mailer.php ' . ($mailer_config ? 'found' : 'NOT FOUND') . '</div>';
        
        if ($composer_exists && $mailer_config) {
            echo '<div class="check pass"><strong>✅</strong> PHPMailer should be available for email sending</div>';
        }
        ?>

        <!-- 4. ERROR LOGS CHECK -->
        <h2>4. Logs Directory Check</h2>
        <?php
        $log_dir = ROOT_PATH . '/logs/';
        $sms_log = $log_dir . 'sms_log.txt';
        
        if (is_dir($log_dir)) {
            echo '<div class="check pass"><strong>✅</strong> logs/ directory exists</div>';
            
            if (file_exists($sms_log)) {
                $size = filesize($sms_log);
                echo '<div class="check pass"><strong>✅</strong> sms_log.txt exists (' . filesize($sms_log) . ' bytes)</div>';
                
                if ($size > 0) {
                    $lines = array_slice(file($sms_log, FILE_SKIP_EMPTY_LINES), -10);
                    echo '<div style="margin: 10px 0;"><strong>Recent SMS log entries (last 10):</strong></div>';
                    echo '<div class="log">';
                    foreach ($lines as $line) {
                        echo htmlspecialchars($line) . "\n";
                    }
                    echo '</div>';
                } else {
                    echo '<div class="check warn"><strong>⚠️</strong> sms_log.txt is empty - SMS code may not be running</div>';
                }
            } else {
                echo '<div class="check warn"><strong>⚠️</strong> sms_log.txt does not exist yet - will be created on first SMS attempt</div>';
            }
        } else {
            echo '<div class="check fail"><strong>❌</strong> logs/ directory not found</div>';
        }
        ?>

        <!-- 5. PHP ERROR LOG -->
        <h2>5. PHP Error Log Check</h2>
        <?php
        $error_log_path = ini_get('error_log');
        if ($error_log_path && file_exists($error_log_path)) {
            echo '<div class="check pass"><strong>✅</strong> PHP error_log: <code>' . htmlspecialchars($error_log_path) . '</code></div>';
            echo '<p style="color: #666; font-size: 12px;">Check this file for PHP errors. On Windows XAMPP, usually: <code>C:\\xampp\\apache\\logs\\error.log</code></p>';
        } else {
            echo '<div class="check warn"><strong>⚠️</strong> PHP error_log path not configured or file not found</div>';
            echo '<p style="color: #666; font-size: 12px;">On XAMPP Windows, check: <code>C:\\xampp\\apache\\logs\\error.log</code></p>';
        }
        ?>

        <!-- 6. TEST SMS SEND -->
        <h2>6. Manual SMS Test</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_phone'])) {
            $test_phone = trim($_POST['test_phone']);
            echo '<div style="margin: 10px 0;"><strong>Testing SMS to: ' . htmlspecialchars($test_phone) . '</strong></div>';
            
            try {
                $result = SmsNotifier::sendRawSMS($test_phone, 'AGAP-Link Test SMS - ' . date('Y-m-d H:i:s'));
                echo '<div class="check pass"><strong>✅ SMS sent successfully!</strong></div>';
                echo '<p>Check your phone for the message. It may take 1-2 seconds to arrive.</p>';
                echo '<p>API Response: <code>' . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . '</code></p>';
            } catch (Exception $e) {
                echo '<div class="check fail"><strong>❌ SMS Failed:</strong></div>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p>Check <code>logs/sms_log.txt</code> for details.</p>';
            }
        }
        ?>
        
        <form method="POST" style="margin: 20px 0;">
            <label for="test_phone"><strong>Test Phone Number (09XXXXXXXXX or 639XXXXXXXXX):</strong></label><br>
            <input type="text" name="test_phone" id="test_phone" placeholder="09123456789" style="padding: 8px; width: 200px; margin: 10px 0;">
            <button type="submit">Send Test SMS</button>
        </form>

        <!-- 7. RECOMMENDATIONS -->
        <h2>7. Next Steps</h2>
        <div class="check warn">
            <h3>If SMS is not working:</h3>
            <ol>
                <li><strong>Check phone format:</strong> Ensure all numbers in database are format <code>09XXXXXXXXX</code> or <code>639XXXXXXXXX</code></li>
                <li><strong>Check API key:</strong> Verify your PhilSMS API key is valid at <strong>https://dashboard.philsms.com</strong></li>
                <li><strong>Check error logs:</strong> Look in <code>logs/sms_log.txt</code> for HTTP error codes</li>
                <li><strong>Test manually:</strong> Use the form above to send a test SMS</li>
                <li><strong>Check firewall:</strong> Ensure your server can reach <code>dashboard.philsms.com</code></li>
                <li><strong>Check logs:</strong> Look for PHP errors in XAMPP error log</li>
            </ol>
        </div>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
        <p style="color: #666; font-size: 12px;">Generated: <?= date('Y-m-d H:i:s') ?></p>
    </div>
</body>
</html>
