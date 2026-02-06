<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: /agap_link/login/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../agap_link/assets/favicon_io/favicon.ico">
    <title>User Dashboard - AGAP-Link</title>
    <link rel="stylesheet" href="../agap_link/assets/css/style.css">
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    <p>You are now logged in.</p>
    
<a href="/agap_link/login/logout.php">Logout</a>
</body>
</html>
