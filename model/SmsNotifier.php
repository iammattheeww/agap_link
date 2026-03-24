<?php

require_once dirname(__DIR__) . "/config/agaplinkdb.php";
require_once MODEL_PATH . 'Report.php';


class SmsNotifier
{
    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Send a status-update SMS for a report.
     * Fetches the reporter's phone from DB, auto-generates the message.
     */
    public static function sendStatusUpdate($reportId, $newStatus): array
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

    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Send a raw, free-form SMS to any PH phone number or multiple numbers.
     * Used for OTPs, login tokens, announcement blasts, and any custom message.
     *
     * @param  string $phone   Raw phone number(s) in any PH format (09XXXXXXXXX, 639XXXXXXXXX, or comma-separated)
     * @param  string $message The full SMS body to send
     * @return array           Decoded API response
     */
    public static function sendRawSMS(string $phone, string $message): array
    {
        // Handle comma-separated phone numbers (bulk send)
        if (strpos($phone, ',') !== false) {
            $phoneList = array_map('trim', explode(',', $phone));
            $formattedPhones = [];
            foreach ($phoneList as $p) {
                $formatted = self::formatPhone($p);
                if ($formatted) {
                    $formattedPhones[] = $formatted;
                }
            }
            if (empty($formattedPhones)) {
                throw new Exception("No valid phone numbers found in bulk send list.");
            }
            $phoneStr = implode(',', $formattedPhones);
        } else {
            // Single phone number
            $formatted = self::formatPhone($phone);
            if (!$formatted) {
                throw new Exception("Invalid phone number: \"$phone\". Expected 09XXXXXXXXX or 639XXXXXXXXX.");
            }
            $phoneStr = $formatted;
        }
        return self::sendSMS($phoneStr, $message);
    }

    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Normalize Philippine phone numbers to 639XXXXXXXXX (PhilSMS requirement).
     */
    private static function formatPhone(string $phone): ?string
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

    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Build a rich, professional status-update message for the citizen.
     * Task 6: Rewritten with warm, informative, empathetic wording.
     */
    private static function buildMessage(int $reportId, string $status): string
    {
        return match ($status) {
            'Pending' =>
            "AGAP-Link — Report Received ✔\n\n" .
                "Hello! Your community report (Ref. #$reportId) has been successfully submitted to AGAP-Link. " .
                "Our admin team has received your concern and it is currently under initial review. " .
                "We appreciate you taking the time to help improve your community. " .
                "You will be notified as soon as there are updates on your report.\n\n" .
                "Thank you for being a responsible citizen.\n– The AGAP-Link Team",

            'Verified' =>
            "AGAP-Link — Report Verified ✔\n\n" .
                "Great news! Your report (Ref. #$reportId) has been reviewed and officially verified by our admin team. " .
                "This means your concern has been assessed as legitimate and is now eligible for further action. " .
                "We are currently identifying the appropriate government agency to handle your concern.\n\n" .
                "Stay tuned for further updates. We are on it!\n– The AGAP-Link Team",

            'Forwarded' =>
            "AGAP-Link — Report Forwarded 📨\n\n" .
                "Your verified report (Ref. #$reportId) has been officially forwarded to the relevant local government authority or agency for proper action. " .
                "The concerned office is now aware of your report and is expected to act on it accordingly. " .
                "We will continue to monitor the progress and notify you of any status changes.\n\n" .
                "Your report is making a difference.\n– The AGAP-Link Team",

            'Ongoing' =>
            "AGAP-Link — Action in Progress 🔧\n\n" .
                "Update on your report (Ref. #$reportId): The assigned agency or local authority is now actively working on addressing your concern. " .
                "Field teams or responsible officers have been deployed or are in the process of resolving the issue you reported. " .
                "We appreciate your patience as this matter is being handled.\n\n" .
                "Thank you for your continued trust in AGAP-Link.\n– The AGAP-Link Team",

            'Resolved' =>
            "AGAP-Link — Report Resolved ✅\n\n" .
                "We are pleased to inform you that your report (Ref. #$reportId) has been officially marked as resolved by the handling agency. " .
                "The concern you raised has been addressed and the necessary actions have been taken. " .
                "We hope this resolution meets your expectations. If the issue persists or resurfaces, please do not hesitate to submit a new report.\n\n" .
                "Thank you for your contribution in making our community a better place.\n– The AGAP-Link Team",

            default =>
            "AGAP-Link — Report Status Update\n\n" .
                "Your report (Ref. #$reportId) has been updated. Its current status is now: $status. " .
                "Please log in to your AGAP-Link account to view the full details and any remarks associated with this update.\n\n" .
                "– The AGAP-Link Team"
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Core PhilSMS API v3 call.
     *
     * IMPORTANT: sender_id must be present. PhilSMS rejects requests with an
     * empty or missing sender_id (HTTP 404). Use 'PhilSMS' (the platform's
     * built-in default) unless you have a custom sender ID approved in your
     * PhilSMS dashboard under Sender ID Management.
     */
    private static function sendSMS(string $phone, string $message): array
    {
        $apiKey   = defined('PHILSMS_API_KEY')   ? trim(PHILSMS_API_KEY)   : '';
        $url      = defined('PHILSMS_API_URL')   ? PHILSMS_API_URL         : 'https://dashboard.philsms.com/api/v3/sms/send';
        $senderId = defined('PHILSMS_SENDER_ID') ? trim(PHILSMS_SENDER_ID) : 'PhilSMS';

        if (empty($apiKey)) {
            throw new Exception("PHILSMS_API_KEY is not configured in config/sms_config.php.");
        }

        $payload = [
            'recipient' => $phone,
            'sender_id' => $senderId,
            'type'      => 'plain',
            'message'   => $message,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception("PhilSMS cURL error: $err");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log every attempt for audit trail
        file_put_contents(
            __DIR__ . '/../logs/sms_log.txt',
            date('Y-m-d H:i:s') . " | HTTP:$httpCode | TO:$phone | SENDER:$senderId | MSG:" .
                mb_substr($message, 0, 60) . '... | RESP:' . $response . "\n",
            FILE_APPEND
        );

        $result = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errMsg = (is_array($result) && isset($result['message']))
                ? $result['message']
                : $response;
            throw new Exception("SMS API failed (HTTP $httpCode): $errMsg");
        }

        return is_array($result) ? $result : [];
    }
}
