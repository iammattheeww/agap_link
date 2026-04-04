<?php
require_once __DIR__ . '/../config/agaplinkdb.php';

class User
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function new_user($first_name, $middle_initial, $last_name, $email, $phone_number, $password)
    {
        $NOW = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $NOW = $NOW->format('Y-m-d H:i:s');

        $sql = "INSERT INTO users (first_name, middle_initial, last_name, email, phone_number, password_hash, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        try {
            $this->conn->beginTransaction();
            $stmt->execute([
                $first_name,
                $middle_initial,
                $last_name,
                $email,
                $phone_number,
                $password,
                $NOW
            ]);
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function check_login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $user = $q->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    public function admin_check_login($email, $password)
    {
        $sql = "SELECT * FROM admin_users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $admin = $q->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        return false;
    }

    // NEW METHOD FOR AGENCY LOGIN CHECK
    public function agency_check_login($email, $password)
    {

        // SQL QUERY WITH ALIASES
        // ---------------------------------------------------------------- //

        // $sql = "SELECT au.*, a.name as agency_name 
        //         FROM agency_users au
        //         JOIN agencies a ON au.agency_id = a.agency_id
        //         WHERE au.email = :email AND au.is_active = 1";

        // ---------------------------------------------------------------- //

        // SQL QUERY WITHOUT ALIASES
        // ---------------------------------------------------------------- //

        $sql = "SELECT agency_users.*, agencies.name as agency_name 
        FROM agency_users
        JOIN agencies ON agency_users.agency_id = agencies.agency_id
        WHERE agency_users.email = :email AND agency_users.is_active = 1";

        // ---------------------------------------------------------------- //

        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $agency_user = $q->fetch(PDO::FETCH_ASSOC);

        if ($agency_user && password_verify($password, $agency_user['password_hash'])) {
            return $agency_user;
        }

        return false;
    }

    // TIMESTAMP FOR WHEN THE AGENCY LAST LOGGED IN
    public function update_agency_last_login($agency_user_id)
    {
        $sql = "UPDATE agency_users SET last_login = NOW() WHERE agency_user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$agency_user_id]);
    }

    // TIMESTAMP FOR WHEN THE ADMIN LAST LOGGED IN
    public function update_admin_last_login($admin_id)
    {
        $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$admin_id]);
    }

    // TIMESTAMP FOR WHEN THE USER LAST LOGGED IN
    public function update_user_last_login($user_id)
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id]);
    }

    public function get_user_id($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $user_id = $q->fetchColumn();
        return $user_id ? $user_id : false;
    }

    public function get_user_name($id)
    {
        $sql = "SELECT CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_name = $q->fetchColumn();
        return $user_name ? $user_name : false;
    }

    public function get_user_first_name($id)
    {
        $sql = "SELECT first_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $first_name = $q->fetchColumn();
        return $first_name ? $first_name : false;
    }

    public function get_user_last_name($id)
    {
        $sql = "SELECT last_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $last_name = $q->fetchColumn();
        return $last_name ? $last_name : false;
    }

    public function get_user_middle_initial($id)
    {
        $sql = "SELECT middle_initial FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $middle_initial = $q->fetchColumn();
        return $middle_initial;
    }

    public function get_user_email($id)
    {
        $sql = "SELECT email FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_email = $q->fetchColumn();
        return $user_email ? $user_email : false;
    }

    public function get_user_phone($id)
    {
        $sql = "SELECT phone_number FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_phone = $q->fetchColumn();
        return $user_phone ? $user_phone : false;
    }

    public function get_user_details($id)
    {
        $sql = "SELECT user_id, first_name, middle_initial, last_name,
                       CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name,
                       email, phone_number, password_hash, created_at
                FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail(string $email): array|false
    {
        $sql = "SELECT user_id, first_name, middle_initial, last_name,
                       email, phone_number, password_hash, created_at
                FROM users
                WHERE email = :email
                LIMIT 1";
        $q = $this->conn->prepare($sql);
        $q->execute([':email' => $email]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    public function list_users()
    {
        $sql = "SELECT user_id, first_name, middle_initial, last_name,
                       CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name,
                       email, phone_number, created_at
                FROM users ORDER BY created_at DESC";
        $q = $this->conn->query($sql) or die("failed!");

        $data = [];
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $r;
        }

        return empty($data) ? false : $data;
    }

    public function update_user($id, $first_name, $middle_initial, $last_name, $email, $phone_number)
    {
        $sql = "UPDATE users
                SET first_name = :first_name,
                    middle_initial = :middle_initial,
                    last_name = :last_name,
                    email = :email,
                    phone_number = :phone_number
                WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $result = $q->execute([
            ':first_name'     => $first_name,
            ':middle_initial' => $middle_initial,
            ':last_name'      => $last_name,
            ':email'          => $email,
            ':phone_number'   => $phone_number,
            ':id'             => $id
        ]);
        return $result;
    }

    public function delete_user($id)
    {
        $sql = "DELETE FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $result = $q->execute(['id' => $id]);
        return $result;
    }

    public function get_session()
    {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) {
            return true;
        } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] == true) {
            return true;
        }
        return false;
    }

    public function email_exists($email)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $count = $q->fetchColumn();
        return $count > 0;
    }

    public function update_password(int $user_id, string $hashed_password): bool
    {
        $sql  = "UPDATE users SET password_hash = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $hashed_password,
            ':user_id'  => $user_id,
        ]);
    }

    // GET ALL PHONE NUMBERS FOR SMS BLAST
    public function getAllPhoneNumbers(): array
    {
        $stmt = $this->conn->query(
            "SELECT phone_number FROM users WHERE phone_number IS NOT NULL AND phone_number != ''"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
