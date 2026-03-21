<?php

require_once dirname(__DIR__) . "/config/agaplinkdb.php";

// sms_config.php defines PHILSMS_API_KEY and PHILSMS_API_URL
// It is loaded by init.php, which is always required before SmsNotifier is called.
// The constants are available here at runtime.

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
        // Strip everything that is not a digit
        $phone = preg_replace('/\D/', '', $phone);

        // 09XXXXXXXXX → 639XXXXXXXXX
        if (preg_match('/^09\d{9}$/', $phone)) {
            return '63' . substr($phone, 1);
        }

        // 639XXXXXXXXX → already correct
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
        // Use constants defined in config/sms_config.php (loaded via init.php)
        $apiKey = defined('PHILSMS_API_KEY') ? trim(PHILSMS_API_KEY) : '';
        $url    = defined('PHILSMS_API_URL') ? PHILSMS_API_URL : 'https://dashboard.philsms.com/api/v3/sms/send';

        if (empty($apiKey)) {
            throw new Exception("PHILSMS_API_KEY is not configured in config/sms_config.php.");
        }

        // ⚠️ DO NOT include "sender_id" in this payload.
        // PhilSMS requires sender IDs to be pre-registered and approved in your
        // dashboard under Sender ID Management before they can be used.
        // Using any unregistered sender_id — including "AGAPLink" — will cause
        // the API to reject every request with:
        // "Sender ID is not authorized to send this message."
        // Omitting sender_id makes PhilSMS use your account's default sender,
        // which is authorized automatically.
        $payload = [
            "recipient" => $phone,
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

        // LOG every attempt — success and failure
        file_put_contents(
            __DIR__ . "/../logs/sms_log.txt",
            date('Y-m-d H:i:s') . " | HTTP:$httpCode | TO:$phone | MSG:$message | RESP:$response\n",
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
