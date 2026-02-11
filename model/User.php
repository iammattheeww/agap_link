<?php
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once __DIR__ . '/../config/agaplinkdb.php';
class User
{
    private $conn;

    // CONSTRUCTOR TO INITIALIZE DATABASE CONNECTION
    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // CREATE NEW USER METHOD
    public function new_user($first_name, $middle_initial, $last_name, $email, $phone_number, $password)
    {
        // SETTING TIMEZONE FOR DATABASE TIMESTAMP 
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
            ]);;
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // CHECK USER LOGIN CREDENTIALS METHOD
    public function check_login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $user = $q->fetch(PDO::FETCH_ASSOC);

        // VERIFY PASSWORD USING password_verify()
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    // ADMIN LOGIN CREDENTIALS METHOD
    public function admin_check_login($email, $password)
    {
        $sql = "SELECT * FROM admin_users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $admin = $q->fetch(PDO::FETCH_ASSOC);

        // VERIFY PASSWORD USING password_verify()
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        return false;
    }

    // GET USER ID BY EMAIL
    public function get_user_id($email)
    {
        $sql = "SELECT id FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $user_id = $q->fetchColumn();
        return $user_id ? $user_id : false;
    }

    // GET FULL NAME BY ID (CONCATENATED) METHOD
    public function get_user_name($id)
    {
        $sql = "SELECT CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_name = $q->fetchColumn();
        return $user_name ? $user_name : false;
    }

    // GET USER FIRST NAME BY ID METHOD
    public function get_user_first_name($id)
    {
        $sql = "SELECT first_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $first_name = $q->fetchColumn();
        return $first_name ? $first_name : false;
    }

    // GET USER LAST NAME BY ID METHOD
    public function get_user_last_name($id)
    {
        $sql = "SELECT last_name FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $last_name = $q->fetchColumn();
        return $last_name ? $last_name : false;
    }

    // GET USER MIDDLE INITIAL BY ID METHOD
    public function get_user_middle_initial($id)
    {
        $sql = "SELECT middle_initial FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $middle_initial = $q->fetchColumn();
        return $middle_initial;
    }

    // GET USER EMAIL BY ID METHOD
    public function get_user_email($id)
    {
        $sql = "SELECT email FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_email = $q->fetchColumn();
        return $user_email ? $user_email : false;
    }

    // GET USER PHONE BY ID METHOD
    public function get_user_phone($id)
    {
        $sql = "SELECT phone_number FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_phone = $q->fetchColumn();
        return $user_phone ? $user_phone : false;
    }

    // GET COMPLETE USER DETAILS BY ID METHOD
    public function get_user_details($id)
    {
        $sql = "SELECT user_id, first_name, middle_initial, last_name, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name, email, phone_number, created_at FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    // LIST ALL USERS METHOD
    public function list_users()
    {
        $sql = "SELECT user_id, first_name, middle_initial, last_name, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_initial, '. '), ''), last_name) AS full_name, email, phone_number, created_at FROM users ORDER BY created_at DESC";
        $q = $this->conn->query($sql) or die("failed!");

        $data = [];
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $r;
        }

        return empty($data) ? false : $data;
    }

    // UPDATE USER INFORMATION METHOD
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
            ':first_name' => $first_name,
            ':middle_initial' => $middle_initial,
            ':last_name' => $last_name,
            ':email' => $email,
            ':phone_number' => $phone_number,
            ':id' => $id
        ]);
        return $result;
    }

    // DELETE USER BY ID METHOD
    public function delete_user($id)
    {
        $sql = "DELETE FROM users WHERE user_id = :id";
        $q = $this->conn->prepare($sql);
        $result = $q->execute(['id' => $id]);
        return $result;
    }

    // CHECK IF SESSION IS ACTIVE METHOD
    public function get_session()
    {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) {
            return true;
        } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] == true) {
            return true;
        }
        return false;
    }

    // CHECK IF EMAIL ALREADY EXISTS METHOD
    public function email_exists($email)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $count = $q->fetchColumn();
        return $count > 0;
    }
}
