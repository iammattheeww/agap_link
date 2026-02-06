<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGAP-Link - Your Ultimate City Companion</title>
    <link rel="stylesheet" href="../agap_link/assets/css/style.css">
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
                        <!-- LOGIN BUTTON FOR MOBILE (HIDDEN ON DESKTOP) -->
                        <li class="mobile-only"><a href="/agap_link/login/index.php" class="btn btn-primary mobile-login-btn">Login</a></li>
                    </ul>

                    <div class="nav-actions">
                        <a href="/agap_link/login/index.php" class="btn-link">Login</a>
                        <a href="#" class="btn btn-primary">Get App</a>
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