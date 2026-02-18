<?php
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once MODEL_PATH . 'Announcement.php';

$announcementModel = new Announcement();
$allAnnouncements  = $announcementModel->getAll();

$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>All Announcements - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/landing_page/style.css">
</head>

<body>
    <?php require VIEW_PATH . '/partials/header.php'; ?>

    <section class="announcements" style="background: var(--color-light);">
        <div class="container">

            <div class="announcements-header">
                <div>
                    <span class="section-label">COMMUNITY UPDATES</span>
                    <h1 class="section-title">All Announcements</h1>
                </div>
                <a href="<?= BASE_URL ?>/view/landing_module/index.php" class="btn btn-outline">← Back to Home</a>
            </div>

            <?php if (empty($allAnnouncements)): ?>
                <div style="text-align:center; padding: 80px 20px; color: var(--color-gray-600);">
                    <p style="font-size: 3rem; margin-bottom: 16px;">📢</p>
                    <h2 style="font-family: var(--font-display); color: var(--color-dark); margin-bottom: 10px;">
                        No Announcements Yet
                    </h2>
                    <p>Check back later for community updates and news.</p>
                </div>

            <?php else: ?>
                <div class="announcements-grid">
                    <?php foreach ($allAnnouncements as $ann): ?>
                        <article class="announcement-card">

                            <div class="announcement-image">
                                <div class="announcement-img-placeholder">
                                    <?php if (!empty($ann['image_path'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/announcements/<?= htmlspecialchars(basename($ann['image_path'])) ?>"
                                             alt="<?= htmlspecialchars($ann['title']) ?>"
                                             class="announcement-img">
                                    <?php else: ?>
                                        <img src="<?= ASSET_URL ?>/images/landing_announcement_01.jpg"
                                             alt="Announcement" class="announcement-img">
                                    <?php endif; ?>
                                </div>
                                <span class="announcement-badge">News</span>
                            </div>

                            <div class="announcement-content">
                                <time class="announcement-date">
                                    <?= date('F j, Y', strtotime($ann['created_at'])) ?>
                                    &middot; <?= Announcement::relativeDate($ann['created_at']) ?>
                                </time>
                                <h3 class="announcement-title"><?= htmlspecialchars($ann['title']) ?></h3>
                                <p class="announcement-description">
                                    <?= htmlspecialchars($ann['content']) ?>
                                </p>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <script src="<?= ASSET_URL ?>/js/landing/main.js"></script>

    <?php require VIEW_PATH . 'partials/footer.php'; ?>
</body>

</html>
