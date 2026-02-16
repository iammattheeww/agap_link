<?php
require_once dirname(__DIR__) . "/config/init.php";
require MODEL_PATH . 'User.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'register':
        register_user();
        break;

    case 'login':
        login_user();
        break;

    default:
        $_SESSION['error'] = "Invalid action!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
}

// REGISTER NEW USER 
function register_user()
{
    $user = new User();

    // GET AND SANITIZE INPUT 
    $first_name = trim($_POST['first_name']);
    $middle_initial = isset($_POST['middle_initial']) && !empty(trim($_POST['middle_initial']))
        ? strtoupper(trim($_POST['middle_initial']))
        : null; // OPTIONAL FIELD
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDATE REQUIRED FIELDS
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "Please fill in all required fields!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // VALIDATE MIDDLE INITIAL FORMAT (IF PROVIDED)
    if ($middle_initial !== null && strlen($middle_initial) > 5) {
        $_SESSION['error'] = "Middle initial should be 1-5 characters only!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // CHECK IF EMAIL ALREADY EXISTS
    if ($user->email_exists($email)) {
        $_SESSION['error'] = "This email is already registered!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // 8 CHARACTER PASSWORD VALIDATION  
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // VALIDATE PASSWORD MATCH 
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // PASSWORD HASHING
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // CREATE NEW USER
    try {
        $result = $user->new_user($first_name, $middle_initial, $last_name, $email, $phone, $hashed_password);

        if ($result) {
            $_SESSION['success'] = "Account created successfully. Please log in.";
            header("Location: " . BASE_URL . "/view/auth/index.php");
            die();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }
}

// LOGIN USER
function login_user()
{
    global $conn;
    $user = new User();

    // GET AND SANITIZE INPUT
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // VALIDATE INPUT FIELDS
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields!";
        header("Location: " . BASE_URL . "/view/auth/index.php");
        die();
    }

    // REGULAR USER LOGIN
    $user_data = $user->check_login($email, $password);

    if ($user_data) {
        // SET USER SESSION VARIABLES
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user_data['user_id'];
        $_SESSION['user_first_name'] = $user_data['first_name'];
        $_SESSION['user_middle_initial'] = $user_data['middle_initial'];
        $_SESSION['user_last_name'] = $user_data['last_name'];

        // STORE FULL NAME FOR EASY DISPLAY
        $_SESSION['user_name'] = trim($user_data['first_name'] . ' ' .
            (!empty($user_data['middle_initial']) ? $user_data['middle_initial'] . '. ' : '') .
            $user_data['last_name']);

        $_SESSION['user_email'] = $user_data['email'];
        $_SESSION['user_phone'] = $user_data['phone_number'];

        // REDIRECT USER TO DASHBOARD
        header("Location: " . BASE_URL . "/view/user_module/user_dashboard.php");
        exit();
    }

    $admin_data = $user->admin_check_login($email, $password);
    if ($admin_data) {
        // SET ADMIN SESSION VARIABLES
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin_data['id'];
        $_SESSION['admin_email'] = $admin_data['email'];
        $_SESSION['admin_name'] = $admin_data['name'];

        // REDIRECT ADMIN TO DASHBOARD
        header("Location: " . BASE_URL . "/view/admin_module/admin_dashboard.php");
        exit();
    }

    // LOGIN FAILED MESSAGE
    $_SESSION['error'] = "Invalid email or password!";
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}
