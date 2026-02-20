<?php
/**
 * SmsNotifier.php — Free SMS notification using Semaphore API (Philippines)
 *
 * SETUP:
 * 1. Register free at https://semaphore.co (free tier = 500 SMS/month, no subscription needed)
 * 2. Get your API key from https://semaphore.co/account#api
 * 3. Set SEMAPHORE_API_KEY and SEMAPHORE_SENDER_NAME in config/init.php or define below
 *
 * No PHPMailer or SMTP needed — pure cURL HTTP call.
 */

require_once dirname(__DIR__) . '/config/init.php';

class SmsNotifier
{
    // ── Configuration (override via constants in init.php) ──
    private static function apiKey(): string
    {
        return defined('SEMAPHORE_API_KEY') ? SEMAPHORE_API_KEY : '';
    }

    private static function senderName(): string
    {
        return defined('SEMAPHORE_SENDER_NAME') ? SEMAPHORE_SENDER_NAME : 'AGAP-Link';
    }

    /**
     * Send an SMS for a report status update.
     *
     * @param int    $report_id  ID of the updated report
     * @param string $new_status New status value
     * @throws Exception         If the send fails
     */
    public static function sendStatusUpdate(int $report_id, string $new_status): void
    {
        global $conn;

        // Fetch reporter phone + first name
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
        $phone     = preg_replace('/\D/', '', $row['phone_number']); // digits only
        $id        = str_pad($report_id, 4, '0', STR_PAD_LEFT);

        $messages = [
            'Pending'   => "Your report (ID: #$id) has been received and is currently pending review. Thank you for reporting, $firstName!",
            'Verified'  => "Good news, $firstName! Your report (ID: #$id) has been verified and is now being assessed.",
            'Forwarded' => "Your report (ID: #$id) has been forwarded to the appropriate agency for action, $firstName.",
            'Ongoing'   => "Update: Your report (ID: #$id) is now being actively addressed. Thank you for your patience, $firstName.",
            'Resolved'  => "Great news, $firstName! Your report (ID: #$id) has been resolved. Thank you for helping improve our community!",
        ];

        $message = $messages[$new_status] ?? "Your AGAP-Link report #$id status has been updated to: $new_status.";

        self::sendSms($phone, $message);
    }

    /**
     * Send a custom SMS message to a phone number.
     * Used by admin to message a citizen with a custom text.
     */
    public static function sendCustomSms(string $phone, string $message): void
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (empty($phone)) {
            throw new Exception('No valid phone number provided.');
        }
        self::sendSms($phone, $message);
    }

    /**
     * Send a raw SMS via Semaphore API.
     *
     * @param string $to      Recipient number (digits only, e.g. 09171234567)
     * @param string $message SMS body
     * @throws Exception
     */
    private static function sendSms(string $to, string $message): void
    {
        $apiKey = self::apiKey();

        if (empty($apiKey)) {
            throw new Exception('SEMAPHORE_API_KEY is not configured.');
        }

        $payload = http_build_query([
            'apikey'      => $apiKey,
            'number'      => $to,
            'message'     => $message,
            'sendername'  => self::senderName(),
        ]);

        $ch = curl_init('https://api.semaphore.co/api/v4/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new Exception("SMS cURL error: $curlErr");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("SMS API returned HTTP $httpCode: $response");
        }

        // Log success
        error_log("SMS sent to $to (status: $httpCode)");
    }
}
