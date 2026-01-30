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
    <title>Dashboard - AGAP-Link</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    <p>You are now logged in.</p>
    
<a href="/agap_link/login/logout.php">Logout</a>
</body>
</html>
