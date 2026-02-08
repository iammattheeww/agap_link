<?php
session_start();

// DESTROY ALL SESSION DATA
session_unset();
session_destroy();

session_start();
$_SESSION['success'] = "You have been logged out successfully.";

// REDIRECT TO LANDING PAGE
header("Location: /agap_link/index.php");
exit;
