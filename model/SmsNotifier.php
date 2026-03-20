<?php

require_once dirname(__DIR__) . "/config/agaplinkdb.php";
require_once MODEL_PATH . 'Report.php';


class SmsNotifier
{
    // ─────────────────────────────────────────────
    public static function sendStatusUpdate($reportId, $newStatus)
    {
        $reportModel = new Report();
        $report = $reportModel->getReportById($reportId);

        if (!$report) {
            throw new Exception("Report not found.");
        }

        if (empty($report['reporter_phone'])) {
            throw new Exception("No phone number.");
        }

        $phone = self::formatPhone($report['reporter_phone']);

        if (!$phone) {
            throw new Exception("Invalid phone format.");
        }

        $message = self::buildMessage($reportId, $newStatus);

        return self::sendSMS($phone, $message);
    }
    // ─────────────────────────────────────────────

    // ─────────────────────────────────────────────
    private static function formatPhone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (preg_match('/^09\d{9}$/', $phone)) {
            return '63' . substr($phone, 1);
        }

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
        $apiKey = "1624|ulZCKZqRcxrUEcKSshGZLkTgkF6ArDU3Bosvb3be53970c83";

        $url = "https://dashboard.philsms.com/api/v3/sms/send";

        // ADDED sender_id AND type - WITHOUT THEM WILL THEREFORE RESULT TO THE API REJECTING OR SILENTLY DROPPING THE REQUEST
        $payload = [
            "recipient" => $phone,
            "sender_id" => "AGAPLink",
            "type"      => "plain",
            "message"   => $message
        ];


        $headers = [
            "Authorization: Bearer " . trim($apiKey),
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // LOG EVERYTHING (RECORDS SMS MESSAGES)
        file_put_contents(
            __DIR__ . "/../logs/sms_log.txt",
            date('Y-m-d H:i:s') . " | HTTP:$httpCode | $phone | $response\n",
            FILE_APPEND
        );

        $result = json_decode($response, true);

        // THROW ERROR IF FAILED
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("SMS API failed: " . $response);
        }

        return $result;
    }
}
