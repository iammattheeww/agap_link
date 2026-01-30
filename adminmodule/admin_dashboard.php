<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /agap_link/login/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - AGAP-Link</title>
</head>
<body>

<h1>
    Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?> (Admin)
</h1>

<p>This is the admin dashboard.</p>

<a href="/agap_link/login/logout.php">Logout</a>

</body>
</html>
