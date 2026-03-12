<?php
// GET CURRENT PAGE FILENAME
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ADMIN DASHBOARD SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h1 class="logo">AGAP-Link</h1>
        <p class="user-panel-label">ADMIN PANEL</p>
    </div>

    <nav class="sidebar-nav">
        <!-- Sliding active indicator -->
        <span class="active-indicator"></span>

        <a href="<?= BASE_URL ?>/view/admin_module/admin_dashboard.php"
           class="nav-item <?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span>
            <span>Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>/view/admin_module/admin_report.php"
           class="nav-item <?= ($current_page == 'admin_report.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="file-text"></i></span>
            <span>Reports</span>
        </a>

        <a href="<?= BASE_URL ?>/view/admin_module/archived_reports.php"
           class="nav-item <?= ($current_page == 'archived_reports.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="archive"></i></span>
            <span>Archived</span>
        </a>

        <a href="<?= BASE_URL ?>/view/admin_module/admin_users.php"
           class="nav-item <?= ($current_page == 'admin_users.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="users"></i></span>
            <span>Users</span>
        </a>

        <a href="<?= BASE_URL ?>/view/admin_module/announcement.php"
           class="nav-item <?= ($current_page == 'announcement.php') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="bell"></i></span>
            <span>Announcements</span>
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

function moveIndicator() {
    const active = document.querySelector(".sidebar-nav .nav-item.active");
    const indicator = document.querySelector(".active-indicator");
    if (!active || !indicator) return;
    indicator.style.height = active.offsetHeight + "px";
    indicator.style.transform = `translateY(${active.offsetTop}px)`;
}

window.addEventListener("DOMContentLoaded", () => {
    moveIndicator();
    setTimeout(moveIndicator, 50);
});
window.addEventListener("resize", moveIndicator);
</script>
