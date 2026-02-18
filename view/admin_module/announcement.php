<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once dirname(__DIR__, 2) . "/config/agaplinkdb.php";

// Fetch announcements dynamically
$announcements = [];
try {
    $stmt = $conn->query("SELECT a.*, ad.name AS author_name FROM announcements a LEFT JOIN admin_users ad ON a.created_by = ad.id ORDER BY a.created_at DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table may not exist yet; silently fail
}

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
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

        <main class="main-content page-transition">

            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">Announcements</h1>
                    <p class="welcome-subtitle">Publish and manage community announcements.</p>
                </div>
                <button class="btn-add-announcement" id="openAnnouncementModal">
                    + New Announcement
                </button>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <section class="announcements-section">
                <h2 class="section-title">Published Announcements</h2>

                <?php if (empty($announcements)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📢</div>
                        <p class="empty-message">No announcements have been published yet.</p>
                        <button class="btn-submit-first" id="openAnnouncementModalEmpty">
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
                                        <span>&#x1F464; <?= htmlspecialchars($a['author_name'] ?? 'Admin') ?></span>
                                    </div>
                                </div>
                                <span class="status-badge status-active">Published</span>
                            </div>
                            <?php if (!empty($a['image_path'])): ?>
                                <img src="<?= htmlspecialchars(UPLOAD_URL . '/announcements/' . basename($a['image_path'])) ?>"
                                     alt="Announcement image"
                                     style="width:100%; max-height:220px; object-fit:cover;">
                            <?php endif; ?>
                            <div class="announcement-body">
                                <?= nl2br(htmlspecialchars($a['content'])) ?>
                            </div>
                            <div class="announcement-footer">
                                <form method="POST" action="<?= BASE_URL ?>/controller/announcement_process.php"
                                      onsubmit="return confirm('Delete this announcement?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="announcement_id" value="<?= $a['announcement_id'] ?>">
                                    <button type="submit" class="action-delete" style="border:none; cursor:pointer; padding:6px 12px; border-radius:6px; font-size:0.82rem; font-weight:600;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <!-- NEW ANNOUNCEMENT MODAL -->
    <div class="report-modal-overlay" id="announcementModal">
        <div class="report-modal" style="max-width: 620px;">
            <div class="modal-header">
                <h2 style="font-family: var(--font-display); color: var(--color-secondary);">New Announcement</h2>
                <button class="modal-close" id="closeAnnouncementModal">&times;</button>
            </div>
            <p class="modal-subtitle">Create a new community announcement.</p>

            <form method="POST" action="<?= BASE_URL ?>/controller/announcement_process.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label class="form-label" for="ann_title">Title *</label>
                    <input type="text" id="ann_title" name="title" class="form-input"
                           placeholder="Announcement title" required maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label" for="ann_content">Content / Body *</label>
                    <textarea id="ann_content" name="content" class="form-input"
                              placeholder="Write the announcement details here..." required
                              style="min-height: 140px;"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Image <span class="form-label-optional">(Optional)</span></label>
                    <div class="file-upload-area" id="annFileUploadArea">
                        <div class="upload-icon">🖼️</div>
                        <div class="upload-text">Click to upload an image</div>
                        <div class="upload-hint">PNG, JPG, JPEG up to 5MB</div>
                    </div>
                    <input type="file" name="image" id="annImageInput" class="file-input-hidden"
                           accept="image/png, image/jpeg, image/jpg">
                    <div id="annPreviewContainer" class="preview-container">
                        <img src="" alt="Preview" class="preview-image" id="annPreviewImage">
                        <button type="button" class="remove-image-btn" id="annRemoveImageBtn">Remove Image</button>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-primary">Publish</button>
                    <button type="button" class="btn-secondary" id="cancelAnnouncementModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">☰</button>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>

    <script>
        const annModal = document.getElementById('announcementModal');

        function openModal() { annModal.classList.add('active'); }
        function closeModal() { annModal.classList.remove('active'); }

        document.getElementById('openAnnouncementModal').addEventListener('click', openModal);
        document.getElementById('closeAnnouncementModal').addEventListener('click', closeModal);
        document.getElementById('cancelAnnouncementModal').addEventListener('click', closeModal);

        const emptyBtn = document.getElementById('openAnnouncementModalEmpty');
        if (emptyBtn) emptyBtn.addEventListener('click', openModal);

        annModal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // File upload preview
        const uploadArea = document.getElementById('annFileUploadArea');
        const fileInput  = document.getElementById('annImageInput');
        const preview    = document.getElementById('annPreviewContainer');
        const previewImg = document.getElementById('annPreviewImage');
        const removeBtn  = document.getElementById('annRemoveImageBtn');

        uploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            preview.style.display = 'none';
        });
    </script>
</body>

</html>
