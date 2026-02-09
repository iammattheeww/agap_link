<?php
$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$user_name = $is_user_logged_in ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <!-- <link rel="stylesheet" href="../agap_link/assets/css/style.css"> -->
    <link rel="stylesheet" href="/agap_link/assets/css/landing_page/style.css">
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="container">
                <div class="nav-wrapper">
                    <div class="nav-brand">
                        <a href="index.php" class="logo">AGAP-Link</a>
                    </div>

                    <ul class="nav-menu" id="navMenu">
                        <li><a href="#home" class="nav-link">Home</a></li>
                        <li><a href="#services" class="nav-link">Services</a></li>
                        <li><a href="#announcements" class="nav-link">Announcements</a></li>
                        <li><a href="#contact" class="nav-link">Contact</a></li>

                        <!-- LOGGED IN USER BUTTONS -->
                        <?php if ($is_user_logged_in): ?>
                            <!-- MOBILE ONLY: DASHBOARD CTA -->
                            <li class="mobile-only">
                                <a href="/agap_link/view/user_module/user_dashboard.php" class="nav-link">Dashboard</a>
                            </li>
                            <!-- MOBILE ONLY: LOGOUT BUTTON-->
                            <li class="mobile-only">
                                <a href="/agap_link/view/auth/logout.php" class="nav-link">Logout</a>
                            </li>
                        <?php else: ?>
                            <!-- MOBILE ONLY: LOGIN BUTTON -->
                            <li class="mobile-only">
                                <a href="/agap_link/view/auth/index.php" class="btn btn-primary mobile-login-btn">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="nav-actions">
                        <!-- IF USER IS LOGGED IN, IT DISPLAYS THE USERNAME, THE DASHBOARD, AND LOGOUT BUTTONS -->
                        <?php if ($is_user_logged_in): ?>
                            <!-- IF USER IS LOGGED IN, IT SHOWS THE USERNAME GREETING, THE DASHBOARD AND LOGOUT BUTTONS -->
                            <span class="user-greeting">Hello, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
                            <a href="/agap_link/view/user_module/user_dashboard.php" class="btn btn-outline">Dashboard</a>
                            <a href="/agap_link/view/auth/logout.php" class="btn btn-link">Logout</a>
                        <?php else: ?>
                            <!-- IF USER IS NOT LOGGED IN, IT SHOWS THE LOGIN BUTTON -->
                            <a href="/agap_link/view/auth/index.php" class="btn btn-primary">Login</a>
                        <?php endif; ?>
                        
                        <!-- <a href="/agap_link/login/index.php" class="btn-link">Login</a> -->
                        <!-- <a href="/agap_link/view/auth/index.php" class="btn-link">Login</a> -->
                        <!-- <a href="#" class="btn btn-primary">Get App</a> -->
                    </div>

                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                        <!-- SPAN TAGS FOR HAMBURGER ICON -->
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>