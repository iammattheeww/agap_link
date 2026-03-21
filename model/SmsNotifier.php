<?php

require_once dirname(__DIR__) . "/config/agaplinkdb.php";
require_once MODEL_PATH . 'Report.php';


class SmsNotifier
{
    // ─────────────────────────────────────────────
    public static function sendStatusUpdate($reportId, $newStatus)
    {
        $reportModel = new Report();
        $report      = $reportModel->getReportById($reportId);

        if (!$report) {
            throw new Exception("Report #$reportId not found.");
        }

        if (empty($report['reporter_phone'])) {
            throw new Exception("Reporter for Report #$reportId has no phone number on record.");
        }

        $phone = self::formatPhone($report['reporter_phone']);

        if (!$phone) {
            throw new Exception(
                "Phone number \"{$report['reporter_phone']}\" could not be formatted. " .
                    "Expected 09XXXXXXXXX or 639XXXXXXXXX."
            );
        }

        $message = self::buildMessage($reportId, $newStatus);

        return self::sendSMS($phone, $message);
    }

    // ─────────────────────────────────────────────
    private static function formatPhone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        // 09XXXXXXXXX → 639XXXXXXXXX
        if (preg_match('/^09\d{9}$/', $phone)) {
            return '63' . substr($phone, 1);
        }

        // Already in international format
        if (preg_match('/^639\d{9}$/', $phone)) {
            return $phone;
        }

        return null;
    }

    // ─────────────────────────────────────────────
    private static function buildMessage($reportId, $status)
    {
        return match ($status) {
            'Pending'   => "AGAP-Link: Your report (#$reportId) has been received.",
            'Verified'  => "AGAP-Link: Your report (#$reportId) has been verified.",
            'Forwarded' => "AGAP-Link: Your report (#$reportId) was forwarded to authorities.",
            'Ongoing'   => "AGAP-Link: Your report (#$reportId) is being handled.",
            'Resolved'  => "AGAP-Link: Your report (#$reportId) has been resolved. Thank you!",
            default     => "AGAP-Link: Your report (#$reportId) status updated to $status."
        };
    }

    // ─────────────────────────────────────────────
    private static function sendSMS($phone, $message)
    {
        $apiKey   = defined('PHILSMS_API_KEY')   ? trim(PHILSMS_API_KEY)   : '';
        $url      = defined('PHILSMS_API_URL')   ? PHILSMS_API_URL         : 'https://dashboard.philsms.com/api/v3/sms/send';
        $senderId = defined('PHILSMS_SENDER_ID') ? trim(PHILSMS_SENDER_ID) : 'PhilSMS';

        if (empty($apiKey)) {
            throw new Exception("PHILSMS_API_KEY is not configured in config/sms_config.php.");
        }

        // PhilSMS REQUIRES sender_id — omitting it or passing an empty string
        // causes HTTP 404: "Sender ID is not authorized to send this message."
        // Use "PhilSMS" (the platform default) unless you have a custom
        // sender ID approved in your dashboard.
        $payload = [
            "recipient" => $phone,
            "sender_id" => $senderId,
            "type"      => "plain",
            "message"   => $message,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer " . $apiKey,
                "Content-Type: application/json",
                "Accept: application/json",
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: " . $curlError);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // LOG every attempt
        file_put_contents(
            __DIR__ . "/../logs/sms_log.txt",
            date('Y-m-d H:i:s') . " | HTTP:$httpCode | TO:$phone | SENDER:$senderId | MSG:$message | RESP:$response\n",
            FILE_APPEND
        );

        $result = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorDetail = (is_array($result) && isset($result['message']))
                ? $result['message']
                : $response;
            throw new Exception("SMS API failed (HTTP $httpCode): " . $errorDetail);
        }

        return $result;
    }
}
