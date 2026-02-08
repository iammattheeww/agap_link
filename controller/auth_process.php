<?php
session_start();
require_once __DIR__ . '/../config/agaplinkdb.php';

$action = $_POST['action'] ?? '';

// REGISTER NEW USER 
if ($action === 'register') {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: /agap_link/view/auth/index.php"); // FIXED: ABSOLUTE PATH
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $phone, $hashed_password]);

        $_SESSION['success'] = "Account created successfully. Please log in.";
        header("Location: /agap_link/view/auth/index.php"); // FIXED: ABSOLUTE PATH
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: /agap_link/view/auth/index.php"); // FIXED: ABSOLUTE PATH
        exit();
    }
}

/* LOGIN ACCESS FOR ADMIN */
if ($action === 'login') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ADMIN LOGIN CREDENTIALS
    if ($email === "admin@agap-link.com" && $password === "Admin123!") {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = "Admin";
        header("Location: /agap_link/view/admin_module/admin_dashboard.php"); // FIXED: ABSOLUTE PATH
        exit();
    }

    // USER LOGIN
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: /agap_link/view/user_module/user_dashboard.php"); // FIXED: ABSOLUTE PATH
        exit();
    }

    $_SESSION['error'] = "Invalid email or password!";
    header("Location: /agap_link/view/auth/index.php"); // FIXED: ABSOLUTE PATH
    exit();
}


/* INVALID ACTION */
$_SESSION['error'] = "Invalid action!";
header("Location: /agap_link/view/auth/index.php"); // FIXED: ABSOLUTE PATH
exit();
