<?php
// session_start();

// // DESTROY ALL SESSION DATA
// session_destroy();
// session_unset();
// unset($_SESSION["logged_in"]);
// $_SESSION = array();

// session_start();
// $_SESSION['success'] = "You have been logged out successfully.";

// // REDIRECT TO LANDING PAGE
// header("Location: /agap_link/index.php");
// exit;

session_start();

// UNSET ALL SESSION VARIABLES
$_SESSION = array();

// DESTROY SESSION COOKIE
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// DESTROY SESSION
session_destroy();

// PREVENT BROWSER CACHING OF PROTECTED PAGES
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// START NEW SESSION FOR SUCCESS MESSAGE
session_start();
$_SESSION['success'] = "You have been logged out successfully.";

// REDIRECT TO LANDING PAGE
header("Location: /agap_link/index.php");
exit;
