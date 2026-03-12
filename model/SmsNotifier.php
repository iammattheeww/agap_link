<?php
require_once dirname(__DIR__) . '/config/init.php';

class SmsNotifier
{
    private static function apiKey(): string
    {
        return defined('PHILSMS_API_KEY') ? PHILSMS_API_KEY : '';
    }

    /**
     * Automatically send SMS when a report status changes.
     * Called by update_report_status.php — no manual trigger needed.
     */
    public static function sendStatusUpdate(int $report_id, string $new_status): void
    {
        global $conn;

        $stmt = $conn->prepare("
            SELECT u.first_name, u.phone_number
            FROM reports r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.report_id = ?
        ");
        $stmt->execute([$report_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['phone_number'])) {
            throw new Exception("No phone number found for report #$report_id");
        }

        $firstName = htmlspecialchars_decode($row['first_name']);
        $phone     = self::formatPhone($row['phone_number']);
        $id        = str_pad($report_id, 4, '0', STR_PAD_LEFT);

        $messages = [
            'Pending'   => "Hi $firstName! Your AGAP-Link report (ID: #$id) has been received and is pending review. Thank you for helping keep our community safe!",
            'Verified'  => "Hi $firstName! Good news — your report (ID: #$id) has been verified and is now being assessed by our team.",
            'Forwarded' => "Hi $firstName! Your report (ID: #$id) has been forwarded to the appropriate response agency for action.",
            'Ongoing'   => "Hi $firstName! Update: your report (ID: #$id) is now being actively addressed. Thank you for your patience!",
            'Resolved'  => "Hi $firstName! Your report (ID: #$id) has been resolved. Thank you for helping improve our community! - AGAP-Link",
        ];

        $message = $messages[$new_status]
            ?? "Hi $firstName! Your AGAP-Link report #$id status has been updated to: $new_status.";

        self::send($phone, $message);
    }

    /**
     * Send a custom one-off SMS (still available for edge cases).
     */
    public static function sendCustomSms(string $phone, string $message): void
    {
        $phone = self::formatPhone($phone);
        if (empty($phone)) {
            throw new Exception('No valid phone number provided.');
        }
        self::send($phone, $message);
    }

    /**
     * Normalize PH phone numbers to 639XXXXXXXXX format (required by PhilSMS).
     */
    private static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // digits only

        // 09XXXXXXXXX -> 639XXXXXXXXX
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = '63' . substr($phone, 1);
        }

        // 9XXXXXXXXX -> 639XXXXXXXXX
        if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
            $phone = '63' . $phone;
        }

        return $phone;
    }

    /**
     * Core PhilSMS API call.
     */
    private static function send(string $to, string $message): void
    {
        $apiKey = self::apiKey();

        if (empty($apiKey) || $apiKey === 'YOUR_API_TOKEN_HERE') {
            throw new Exception('PHILSMS_API_KEY is not configured in config/sms_config.php.');
        }

        $payload = json_encode([
            'number'  => $to,
            'message' => $message,
        ]);

        $ch = curl_init(PHILSMS_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new Exception("PhilSMS cURL error: $curlErr");
        }

        $decoded = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errMsg = $decoded['message'] ?? $response;
            throw new Exception("PhilSMS API error (HTTP $httpCode): $errMsg");
        }

        error_log("PhilSMS: SMS sent to $to | Status: $httpCode | Response: $response");
    }
}