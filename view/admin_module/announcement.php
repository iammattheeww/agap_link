<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING - CRITICAL FOR SECURITY
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>Announcements - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/admin_module/admin_module.css">
</head>

<body>
    <div class="dashboard-container">

        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <main class="main-content">

            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">Announcements</h1>
                    <p class="welcome-subtitle">Publish and manage community announcements.</p>
                </div>
                <button class="btn-add-announcement">
                    + New Announcement
                </button>
            </header>

            <section class="announcements-section">
                <h2 class="section-title">Published Announcements</h2>

                <?php
                // TODO: Connect to announcements model and loop through records.
                // Placeholder content below — replace with dynamic data.
                $announcements = []; // $announcementModel->getAll();
                ?>

                <?php if (empty($announcements)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📢</div>
                        <p class="empty-message">No announcements have been published yet.</p>
                        <button class="btn-submit-first">
                            <span>+</span> Create First Announcement
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                        <div class="announcement-card">
                            <div class="announcement-card-header">
                                <div>
                                    <p class="announcement-title"><?= htmlspecialchars($a['title']) ?></p>
                                    <div class="announcement-meta">
                                        <span>&#x1F4C5; <?= date('M d, Y', strtotime($a['created_at'])) ?></span>
                                        <span>&#x1F464; <?= htmlspecialchars($a['author'] ?? 'Admin') ?></span>
                                    </div>
                                </div>
                                <span class="status-badge status-active">Published</span>
                            </div>
                            <div class="announcement-body">
                                <?= htmlspecialchars($a['content']) ?>
                            </div>
                            <div class="announcement-footer">
                                <a href="edit_announcement.php?id=<?= $a['id'] ?>" class="action-edit">Edit</a>
                                <a href="delete_announcement.php?id=<?= $a['id'] ?>"
                                    onclick="return confirm('Delete this announcement?');"
                                    class="action-delete">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
</body>

</html>