<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// 1. WIPE ALL USER DATA FROM THE SESSION
session_unset();

// 2. REGENERATE SESSION ID FOR SECURITY (Prevents session fixation but keeps it alive for the message)
session_regenerate_id(true);

// 3. PREVENT BROWSER CACHING OF PROTECTED PAGES (Back button protection)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// 4. SET THE SUCCESS MESSAGE
$_SESSION['success'] = "You have been logged out successfully.";

// 5. REDIRECT DIRECTLY TO THE LANDING PAGE
header("Location: " . BASE_URL . "/view/landing_module/index.php");
exit();
