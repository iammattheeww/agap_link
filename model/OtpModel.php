<?php
// KEY POINT: THIS MODEL HANDLES BOTH OTPs FOR PASSWORD RESETS AND LOGIN TOKENS, WITH METHODS TO CREATE, VALIDATE, AND MARK THEM AS USED. THE OTPs AND TOKENS HAVE A 5-MINUTE EXPIRATION TO ENHANCE SECURITY.
// THE 5-MINUTE EXPIRATION HAPPENS SERVERS-SIDE WITH DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHEN THE OTP IS INSERTED

require_once dirname(__DIR__) . '/config/agaplinkdb.php';

class OtpModel
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // DELETE EXISTING OTPs for USER
    public function deleteOtpsByUser(int $userId): void
    {
        $stmt = $this->conn->prepare(
            'DELETE FROM password_reset_otps WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    // INSERT NEW OTP WITH 5-MINUTE EXPIRATION
    public function insertOtp(int $userId, string $code, string $channel): void
    {
        // THE 5-MINUTE EXPIRATION IS SET HERE
        $stmt = $this->conn->prepare(
            'INSERT INTO password_reset_otps (user_id, otp_code, channel, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))'
        );

        // THE OTP CODE, USER ID, AND CHANNEL ARE INSERTED INTO THE DATABASE
        $stmt->execute([$userId, $code, $channel]);
    }

    // FIND VALID OTP (CHECKS IF NOT EXPIRED AND NOT USED)
    public function findValidOtp(int $userId, string $code): array|false
    {
        $stmt = $this->conn->prepare(
            'SELECT otp_id FROM password_reset_otps
             WHERE user_id = ? AND otp_code = ? AND used = 0 AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$userId, $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // MARK OTP AS USED AFTER SUCCESSFUL RESET
    public function markOtpUsed(int $otpId): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE password_reset_otps SET used = 1 WHERE otp_id = ?'
        );
        $stmt->execute([$otpId]);
    }

    // LOGIN TOKENS TABLE METHOD (SIMILAR TO OTP BUT FOR LOGIN PURPOSES)
    public function deleteTokensByUser(int $userId): void
    {
        $stmt = $this->conn->prepare(
            'DELETE FROM login_tokens WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    // INSERT LOGIN TOKEN WITH 5-MINUTE EXPIRATION
    public function insertToken(int $userId, string $code): void
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO login_tokens (user_id, token_code, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))'
        );
        $stmt->execute([$userId, $code]);
    }

    // FIND VALID LOGIN TOKEN (CHECKS IF NOT EXPIRED AND NOT USED) — THIS ONE IS WHERE THE LOGIN TOKEN IS VALIDATED
    public function findValidToken(int $userId, string $code): array|false
    {
        $stmt = $this->conn->prepare(
            'SELECT token_id FROM login_tokens
             WHERE user_id = ? AND token_code = ? AND used = 0 AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$userId, $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // MARK LOGIN TOKEN AFTER SUCCESSFUL LOGIN
    public function markTokenUsed(int $tokenId): void
    {
        // THIS QUERY IS USED TO MARK THE LOGIN TOKEN AS USED AFTER A SUCCESSFUL LOGIN, PREVENTING REUSE
        $stmt = $this->conn->prepare(
            'UPDATE login_tokens SET used = 1 WHERE token_id = ?'
        );
        $stmt->execute([$tokenId]);
    }
}
