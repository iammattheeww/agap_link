<?php
require_once __DIR__ . '/../config/agaplinkdb.php';

//  THIS MODEL HANDLES THE TEMPORARY STORAGE OF REGISTRATION DATA AND OTP CODES FOR THE USER REGISTRATION VERIFICATION PROCESS. IT INTERACTS WITH THE registration_verifications TABLE TO CREATE, VALIDATE, AND MANAGE OTP CODES DURING USER SIGN-UP.
class RegistrationVerification
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // STORE TEMPORARY REGISTRATION DATA WITH OTP; CALLED BEFORE USER IS INSERTED INTO USERS TABLE
    public function createVerification(
        string $firstName,
        ?string $middleInitial,
        string $lastName,
        string $email,
        string $phone,
        string $passwordHash,
        string $otpCode
    ): bool {
        $expiresAt = (new DateTime('now', new DateTimeZone('Asia/Manila')))
            ->modify('+5 minutes')
            ->format('Y-m-d H:i:s');

        $sql = "INSERT INTO registration_verifications 
                (temp_first_name, temp_middle_initial, temp_last_name, temp_email, 
                 temp_phone, temp_password_hash, otp_code, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $firstName,
            $middleInitial,
            $lastName,
            $email,
            $phone,
            $passwordHash,
            $otpCode,
            $expiresAt
        ]);
    }

    // FIND AND VALIDATE OTP CODE; CHECKS IF OTP EXISTS, NOT EXPIRED, AND NOT ALREADY USED. 
    public function findValidOtp(string $email, string $otpCode): array|false
    {
        $sql = "SELECT * FROM registration_verifications 
                WHERE temp_email = :email 
                AND otp_code = :otp_code
                AND used = 0
                AND expires_at > NOW()
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email, ':otp_code' => $otpCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //THIS FUNCTION WILL MARK THE OTP AS USED IN THE DATABASE AFTER SUCCESSFUL VERIFICATION, TO PREVENT IT FROM BEING USED AGAIN. THIS IS IMPORTANT FOR SECURITY, ENSURING THAT ONCE AN OTP HAS BEEN USED TO VERIFY A REGISTRATION, IT CANNOT BE REUSED FOR ANOTHER ATTEMPT.
    public function markOtpUsed(int $verificationId): bool
    {
        $sql = "UPDATE registration_verifications SET used = 1 WHERE verification_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$verificationId]);
    }

    // DELETES VERIFICATION RECORD AFTER SUCCESSFUL USER CREATION. THIS HELPS KEEP THE registration_verifications TABLE CLEAN AND PREVENTS OLD VERIFICATION RECORDS FROM PILING UP, WHICH COULD CAUSE CONFUSION OR SECURITY ISSUES IF LEFT IN THE DATABASE.
    public function deleteVerification(int $verificationId): bool
    {
        $sql = "DELETE FROM registration_verifications WHERE verification_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$verificationId]);
    }

    // CLEAN UP ALL EXPIRED VERIFICATION RECORDS FOR A SPECIFIC EMAIL
    public function deleteExpiredVerifications(string $email): bool
    {
        $sql = "DELETE FROM registration_verifications 
            WHERE temp_email = :email AND expires_at < NOW()";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':email' => $email]);
    }

    // THIS CHECKS IF EMAIL HAS PENDING VERIFICATION 
    public function hasPendingVerification(string $email): bool
    {
        // IF MAG CREATE SI USER SANG ACCOUNT TAPOS GIN SARADO NIYA ANG BROWSER, TAPOS MA REGISTER SIYA LIWAT WITH THE SAME EMAIL, I CHECK NA ANG DATABASE IF MAY EXISTING PENDING VERIFICATION PA ANG EMAIL ADDRESS. IF MAY EXISTING PENDING VERIFICATION, THEN MAY CHOICE SI USER NGA MAG HULAT 5 MINUTES PARA NGA MAG EXPIRE ANG CODE OR I ENTER NIYA ANG CODE NGA NAKUHA NIYA FROM THE FIRST ATTEMPT. 
        $sql = "SELECT COUNT(*) FROM registration_verifications 
                WHERE temp_email = :email 
                AND expires_at > NOW()";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
}
