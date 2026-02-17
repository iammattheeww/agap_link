<?php
// GET CURRENT PAGE FILENAME
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- USER DASHBOARD SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h1 class="logo">AGAP-Link</h1>
        <p class="user-panel-label">USER PANEL</p>
    </div>

    <nav class="sidebar-nav">
        <!-- Sliding active indicator -->
        <span class="active-indicator"></span>

        <a href="<?= BASE_URL ?>/view/user_module/user_dashboard.php"
           class="nav-item <?= ($current_page == 'user_dashboard.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span>
            <span>My Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>/view/user_module/my_reports.php"
           class="nav-item <?= ($current_page == 'my_reports.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="file-text"></i></span>
            <span>Reports</span>
        </a>

        <a href="<?= BASE_URL ?>/view/user_module/profile.php"
           class="nav-item <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="user"></i></span>
            <span>Profile</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/view/auth/logout.php" class="nav-item logout">
            <span class="nav-icon"><i data-lucide="log-out"></i></span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- LUCIDE ICONS -->
<script src="https://unpkg.com/lucide@latest"></script>

<script>
lucide.createIcons();

let currentY = 0;

function moveIndicator() {
    const active = document.querySelector(".sidebar-nav .nav-item.active");
    const indicator = document.querySelector(".active-indicator");

    if (!active || !indicator) return;

    const newY = active.offsetTop;

    indicator.style.height = active.offsetHeight + "px";
    indicator.style.transform = `translateY(${newY}px)`;

    currentY = newY;
}

window.addEventListener("DOMContentLoaded", () => {
    moveIndicator();

    // slight delay gives visible animation on page load
    setTimeout(moveIndicator, 50);
});

window.addEventListener("resize", moveIndicator);
</script>

