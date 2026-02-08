<?php
session_start();
require_once __DIR__ . '/../model/User.php';

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
        header("Location: agap_link/view/auth/index.php");
        die();
}

// REGISTER NEW USER 
function register_user()
{
    $user = new User();

    // GET AND SANITIZE INPUT 
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // CHECK IF EMAIL ALREADY EXISTS
    if ($user->email_exists($email)) {
        $_SESSION['error'] = "This email is already registered!";
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
        $result = $user->new_user($name, $email, $phone, $hashed_password);

        if ($result) {
            $_SESSION['success'] = "Account created successfully. Please log in.";
            header("Location: /agap_link/view/auth/index.php");
            die();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: /agap_link/view/auth/index.php");
        die();
    }
}

// LOGIN USER
function login_user()
{
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
    if ($email === "admin@agap-link.com" && $password === "Admin123!") {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = "Admin";
        $_SESSION['admin_email'] = $email;

        // REDIRECTS DIRECTLY TO ADMIN DASHBOARD
        header("Location: /agap_link/view/admin_module/admin_dashboard.php");
        die();
    }

    // REGULAR USER LOGIN
    $user_data = $user->check_login($email, $password);

    if ($user_data) {
        // SET USER SESSION VARIABLES
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_name'] = $user_data['name'];
        $_SESSION['user_email'] = $user_data['email'];
        $_SESSION['user_phone'] = $user_data['phone'];

        // REDIRECT USER TO LANDING PAGE
        header( "Location: /agap_link/index.php");
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
