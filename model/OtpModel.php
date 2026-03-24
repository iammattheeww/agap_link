<?php
require_once dirname(__DIR__) . '/config/agaplinkdb.php';

class OtpModel
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // ── password_reset_otps table ────────────────────────────────────────────

    public function deleteOtpsByUser(int $userId): void
    {
        $stmt = $this->conn->prepare(
            'DELETE FROM password_reset_otps WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    public function insertOtp(int $userId, string $code, string $channel): void
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO password_reset_otps (user_id, otp_code, channel, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))'
        );
        $stmt->execute([$userId, $code, $channel]);
    }

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

    public function markOtpUsed(int $otpId): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE password_reset_otps SET used = 1 WHERE otp_id = ?'
        );
        $stmt->execute([$otpId]);
    }

    // ── login_tokens table ───────────────────────────────────────────────────

    public function deleteTokensByUser(int $userId): void
    {
        $stmt = $this->conn->prepare(
            'DELETE FROM login_tokens WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    public function insertToken(int $userId, string $code): void
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO login_tokens (user_id, token_code, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))'
        );
        $stmt->execute([$userId, $code]);
    }

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

    public function markTokenUsed(int $tokenId): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE login_tokens SET used = 1 WHERE token_id = ?'
        );
        $stmt->execute([$tokenId]);
    }
}
