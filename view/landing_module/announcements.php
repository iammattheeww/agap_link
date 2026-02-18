<?php
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once dirname(__DIR__, 2) . "/config/agaplinkdb.php";

$is_user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

$allAnnouncements = [];
try {
    $stmt = $conn->query(
        "SELECT announcement_id, title, content, image_path, created_at
         FROM announcements
         ORDER BY created_at DESC"
    );
    $allAnnouncements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table may not exist yet
}

/**
 * Convert a datetime string to a human-readable relative label.
 */
function relativeDate(string $dateString): string {
    $date  = new DateTime($dateString);
    $now   = new DateTime();
    $diff  = $now->diff($date);

    $totalDays = (int)$diff->days;

    if ($totalDays === 0)  return 'Today';
    if ($totalDays === 1)  return 'Yesterday';
    if ($totalDays < 7)   return $totalDays . ' days ago';
    if ($totalDays < 14)  return 'Last week';
    if ($totalDays < 31)  return floor($totalDays / 7) . ' weeks ago';
    if ($totalDays < 60)  return 'Last month';
    if ($totalDays < 365) return floor($totalDays / 30) . ' months ago';
    if ($totalDays < 730) return '1 year ago';
    return floor($totalDays / 365) . ' years ago';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>All Announcements - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/landing_page/style.css">
    <style>
        /* ─── Announcements Page Layout ─── */
        .announcements-page {
            padding: 60px 0 80px;
            background: var(--color-light);
            min-height: 60vh;
        }

        .announcements-page-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .announcements-page-header .section-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .announcements-page-header p {
            color: var(--color-gray-600);
        }

        .announcements-list {
            display: grid;
            gap: 32px;
        }

        .announcement-full-card {
            background: var(--color-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: grid;
            grid-template-columns: 280px 1fr;
            transition: box-shadow var(--transition-base), transform var(--transition-base);
        }

        .announcement-full-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .announcement-full-image {
            background: var(--color-gray-200);
            min-height: 220px;
            overflow: hidden;
        }

        .announcement-full-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .announcement-full-img-placeholder {
            width: 100%;
            height: 100%;
            min-height: 220px;
            background: linear-gradient(135deg, var(--color-gray-200), var(--color-gray-100));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .announcement-full-body {
            padding: 28px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .announcement-full-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }

        .announcement-full-date {
            font-size: 0.85rem;
            color: var(--color-gray-600);
        }

        .announcement-full-badge {
            background: var(--color-primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .announcement-full-relative {
            font-size: 0.82rem;
            color: var(--color-gray-600);
            font-style: italic;
        }

        .announcement-full-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-dark);
            font-family: var(--font-display);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .announcement-full-content {
            color: var(--color-gray-600);
            line-height: 1.7;
            font-size: 0.96rem;
            white-space: pre-wrap;
        }

        /* Empty state */
        .announcements-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--color-gray-600);
        }

        .announcements-empty-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .announcement-full-card {
                grid-template-columns: 1fr;
            }

            .announcement-full-image,
            .announcement-full-img-placeholder {
                min-height: 200px;
            }

            .announcement-full-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <?php require VIEW_PATH . '/partials/header.php'; ?>

    <section class="announcements-page">
        <div class="container">
            <div class="announcements-page-header">
                <span class="section-label">COMMUNITY UPDATES</span>
                <h1 class="section-title">All Announcements</h1>
                <p>Stay informed about the latest news and updates from AGAP-Link and the Bacolod City community.</p>
            </div>

            <?php if (empty($allAnnouncements)): ?>
                <div class="announcements-empty">
                    <div class="announcements-empty-icon">📢</div>
                    <h2 style="font-family: var(--font-display); color: var(--color-dark); margin-bottom: 10px;">No Announcements Yet</h2>
                    <p>Check back later for community updates and news.</p>
                </div>

            <?php else: ?>
                <div class="announcements-list">
                    <?php foreach ($allAnnouncements as $ann): ?>
                        <article class="announcement-full-card">

                            <div class="announcement-full-image">
                                <?php if (!empty($ann['image_path'])): ?>
                                    <img src="<?= BASE_URL . '/uploads/announcements/' . htmlspecialchars(basename($ann['image_path'])) ?>"
                                         alt="<?= htmlspecialchars($ann['title']) ?>">
                                <?php else: ?>
                                    <div class="announcement-full-img-placeholder">📢</div>
                                <?php endif; ?>
                            </div>

                            <div class="announcement-full-body">
                                <div class="announcement-full-meta">
                                    <span class="announcement-full-badge">News</span>
                                    <time class="announcement-full-date">
                                        <?= date('F j, Y', strtotime($ann['created_at'])) ?>
                                    </time>
                                    <span class="announcement-full-relative">
                                        · <?= relativeDate($ann['created_at']) ?>
                                    </span>
                                </div>
                                <h2 class="announcement-full-title"><?= htmlspecialchars($ann['title']) ?></h2>
                                <p class="announcement-full-content"><?= htmlspecialchars($ann['content']) ?></p>
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
