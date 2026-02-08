<?php
class User
{
    private $DB_SERVER = 'localhost';
    private $DB_USERNAME = 'root';
    private $DB_PASSWORD = '';
    private $DB_DATABASE = 'agap_link';
    private $conn;

    // CONSTRUCTOR TO INITIALIZE DATABASE CONNECTION
    public function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->DB_SERVER . ";dbname=" . $this->DB_DATABASE,
                $this->DB_USERNAME,
                $this->DB_PASSWORD
            );
            // Set PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // ADDITIONAL ATTRIBUTES
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // CREATE NEW USER
    public function new_user($name, $email, $phone, $password)
    {
        // SETTING TIMEZONE FOR DATABASE TIMESTAMP 
        $NOW = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $NOW = $NOW->format('Y-m-d H:i:s');

        $sql = "INSERT INTO users (name, email, phone, password, created_at) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        try {
            $this->conn->beginTransaction();
            $stmt->execute([$name, $email, $phone, $password, $NOW]);
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }

        // /* SETTING TIMEZONE FOR DATABASE TIMESTAMP  */
        // $NOW = new DateTime('now', new DateTimeZone('Asia/Manila'));
        // $NOW = $NOW->format('Y-m-d H:i:s');

        // $sql = "INSERT INTO users (name, email, phone, password, created_at) VALUES (?,?,?,?,?)";
        // $stmt = $this->conn->prepare($sql);

        // try {
        //     $this->conn->beginTransaction();
        //     $stmt->execute([$name, $email, $phone, $password, $NOW]);
        //     $this->conn->commit();
        // } catch (Exception $e) {
        //     $this->conn->rollback();
        //     throw $e;
        // }

        // return true;
    }

    // CHECK USER LOGIN CREDENTIALS
    public function check_login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $user = $q->fetch(PDO::FETCH_ASSOC);

        // Verify password using password_verify()
        if ($user && password_verify($password, $user['password'])) {
            return $user;
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

    // GET USERNAME BY ID
    public function get_user_name($id)
    {
        $sql = "SELECT name FROM users WHERE id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_name = $q->fetchColumn();
        return $user_name ? $user_name : false;

        // $sql = "SELECT name FROM users WHERE id = :id";
        // $q = $this->conn->prepare($sql);
        // $q->execute(['id' => $id]);
        // $user_name = $q->fetchColumn();
        // return $user_name;
    }

    // GET USER EMAIL BY ID
    public function get_user_email($id)
    {
        $sql = "SELECT email FROM users WHERE id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_email = $q->fetchColumn();
        return $user_email ? $user_email : false;

        // $sql = "SELECT email FROM users WHERE id = :id";
        // $q = $this->conn->prepare($sql);
        // $q->execute(['id' => $id]);
        // $user_email = $q->fetchColumn();
        // return $user_email;
    }

    // GET USER PHONE BY ID
    public function get_user_phone($id)
    {
        $sql = "SELECT phone FROM users WHERE id = :id";
        $q = $this->conn->prepare($sql);
        $q->execute(['id' => $id]);
        $user_phone = $q->fetchColumn();
        return $user_phone ? $user_phone : false;

        // $sql = "SELECT phone FROM users WHERE id = :id";
        // $q = $this->conn->prepare($sql);
        // $q->execute(['id' => $id]);
        // $user_phone = $q->fetchColumn();
        // return $user_phone;
    }

    // LIST ALL USERS
    public function list_users()
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $q = $this->conn->query($sql) or die("failed!");

        $data = [];
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $r;
        }

        return empty($data) ? false : $data;

        // $sql = "SELECT * FROM users";
        // $q = $this->conn->query($sql) or die("failed!");
        // while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
        //     $data[] = $r;
        // }
        // if (empty($data)) {
        //     return false;
        // } else {
        //     return $data;
        // }
    }

    // UPDATE USER INFORMATION
    public function update_user($id, $name, $email, $phone)
    {
        $sql = "UPDATE users 
                SET name = :name, email = :email, phone = :phone 
                WHERE id = :id";
        $q = $this->conn->prepare($sql);
        $result = $q->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':id' => $id
        ]);
        return $result;
    }

    // DELETE USER BY ID
    public function delete_user($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $q = $this->conn->prepare($sql);
        $result = $q->execute(['id' => $id]);
        return $result;
    }

    // CHECK IF SESSION IS ACTIVE
    public function get_session()
    {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) {
            return true;
        } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] == true) {
            return true;
        }
        return false;
    }

    // CHECK IF EMAIL ALREADY EXISTS
    public function email_exists($email)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $q = $this->conn->prepare($sql);
        $q->execute(['email' => $email]);
        $count = $q->fetchColumn();
        return $count > 0;
    }
}
