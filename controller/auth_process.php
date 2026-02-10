

<?php
session_start();
require_once __DIR__ . '/../model/User.php';
require_once dirname(__DIR__) . '/config/agaplinkdb.php';

// $user = new User();
// $action = $_POST['action'] ?? '';

// $action = $_POST['action'] ?? '';
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'register':
        register_user();
        break;

    case 'login':
        login_user();
        break;

    default:
        // INVALID ACTION 
        $_SESSION['error'] = "Invalid action!";
        header("Location: /agap_link/view/auth/index.php");
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
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // VALIDATE MIDDLE INITIAL FORMAT (IF PROVIDED)
    if ($middle_initial !== null && strlen($middle_initial) > 5) {
        $_SESSION['error'] = "Middle initial should be 1-5 characters only!";
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // CHECK IF EMAIL ALREADY EXISTS
    if ($user->email_exists($email)) {
        $_SESSION['error'] = "This email is already registered!";
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // 8 CHARACTER PASSWORD VALIDATION  
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // VALIDATE PASSWORD MATCH 
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // PASSWORD HASHING
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // CREATE NEW USER
    try {
        $result = $user->new_user($first_name, $middle_initial, $last_name, $email, $phone, $hashed_password);

        if ($result) {
            $_SESSION['success'] = "Account created successfully. Please log in.";
            header("Location: /agap_link/view/auth/index.php");
            die();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: /agap_link/view/auth/index.php");
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
        header("Location: /agap_link/view/auth/index.php");
        die();
    }

    // ADMIN LOGIN CREDENTIALS
     $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['name'];
        

        header("Location: /agap_link/view/admin_module/admin_dashboard.php");
        exit();
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

        // REDIRECT USER TO LANDING PAGE
        header("Location: /agap_link/index.php");
        exit();
    }

    // LOGIN FAILED MESSAGE
    $_SESSION['error'] = "Invalid email or password!";
    header("Location: /agap_link/view/auth/index.php");
    exit();
}




// if ($action === 'register') {

//     $name  = trim($_POST['name']);
//     $email = trim($_POST['email']);
//     $phone = trim($_POST['phone']);
//     $password = $_POST['password'];
//     $confirm_password = $_POST['confirm_password'];

//     if ($password !== $confirm_password) {
//         $_SESSION['error'] = "Passwords do not match!";
//         header("Location: /agap_link/view/auth/index.php");
//         exit();
//     }

//     $hashed_password = password_hash($password, PASSWORD_DEFAULT);

//     try {
//         $user->new_user($name, $email, $phone, $hashed_password);
//         $_SESSION['success'] = "Account created successfully. Please log in.";
//         header("Location: /agap_link/view/auth/index.php");
//         exit();
//     } catch (Exception $e) {
//         $_SESSION['error'] = "Email already exists!";
//         header("Location: /agap_link/view/auth/index.php");
//         exit();
//     }
// }

/* LOGIN ACCESS FOR ADMIN AND USERS */
// if ($action === 'login') {

//     $email = trim($_POST['email']);
//     $password = $_POST['password'];

//     // ADMIN LOGIN CREDENTIALS
//     if ($email === "admin@agap-link.com" && $password === "Admin123!") {
//         $_SESSION['admin_logged_in'] = true;
//         $_SESSION['admin_name'] = "Admin";
//         header("Location: /agap_link/view/admin_module/admin_dashboard.php");
//         exit();
//     }

//     // USER LOGIN
//     $user_data = $user->check_login($email, $password);

//     if ($user_data) {
//         $_SESSION['user_logged_in'] = true;
//         $_SESSION['user_id'] = $user_data['id'];
//         $_SESSION['user_name'] = $user_data['name'];
//         $_SESSION['user_email'] = $user_data['email'];
//         header("Location: /agap_link/index.php");
//         exit();
//     }

//     $_SESSION['error'] = "Invalid email or password!";
//     header("Location: /agap_link/view/auth/index.php");
//     exit();
// }


// /* INVALID ACTION */
// $_SESSION['error'] = "Invalid action!";
// header("Location: /agap_link/view/auth/index.php");
// exit();