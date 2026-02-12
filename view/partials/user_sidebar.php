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

<!-- LUCIDE ICONS SCRIPT -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>