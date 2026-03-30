<?php
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Announcement.php';

$announcementModel = new Announcement();
$announcements     = $announcementModel->getAll();

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error']   ?? '';
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
    <style>
        /* ── Export button ───────────────────────────────────── */
        .btn-export-pdf {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #dc2626;          /* red-600 */
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s ease, transform 0.12s ease, box-shadow 0.18s ease;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);
            white-space: nowrap;
        }
        .btn-export-pdf:hover {
            background: #b91c1c;          /* red-700 */
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
        .btn-export-pdf:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .btn-export-pdf svg {
            flex-shrink: 0;
        }

        /* ── Header action group ─────────────────────────────── */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Loading state on the export button */
        .btn-export-pdf.loading {
            opacity: 0.75;
            pointer-events: none;
        }
        .btn-export-pdf.loading .export-label::after {
            content: '…';
        }
    </style>
</head>

<body>
    <?php require VIEW_PATH . 'partials/mobile_topnav_admin.php'; ?>
    <div class="dashboard-container">

        <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

        <main class="main-content page-transition">

            <header class="content-header">
                <div class="welcome-section">
                    <h1 class="welcome-title">Announcements</h1>
                    <p class="welcome-subtitle">Publish and manage community announcements.</p>
                </div>

                <!-- ── Action buttons ──────────────────────────────────── -->
                <div class="header-actions">

                    <!-- Export to PDF -->
                    <a  href="<?= BASE_URL ?>/controller/export_announcements_pdf.php"
                        class="btn-export-pdf"
                        id="exportPdfBtn"
                        title="Download all announcements as a PDF report"
                        <?= empty($announcements) ? 'aria-disabled="true" tabindex="-1" style="opacity:.45;pointer-events:none;"' : '' ?>>
                        <!-- PDF / download icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="11" x2="12" y2="17"/>
                            <polyline points="9 14 12 17 15 14"/>
                        </svg>
                        <span class="export-label">Export to PDF</span>
                    </a>

                    <!-- New Announcement -->
                    <button class="btn-add-announcement" id="openAnnouncementModal">
                        + New Announcement
                    </button>
                </div>
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
                                        <span><?= Announcement::relativeDate($a['created_at']) ?></span>
                                    </div>
                                </div>
                                <span class="status-badge status-active">Published</span>
                            </div>
                            <?php if (!empty($a['image_path'])): ?>
                                <img src="<?= UPLOAD_URL ?>/announcements/<?= htmlspecialchars(basename($a['image_path'])) ?>"
                                    alt="Announcement image"
                                    style="width:100%; max-height:220px; object-fit:cover;">
                            <?php endif; ?>
                            <div class="announcement-body">
                                <?= nl2br(htmlspecialchars($a['content'])) ?>
                            </div>
                            <div class="announcement-footer">
                                <form method="POST"
                                    action="<?= BASE_URL ?>/controller/announcement_process.php"
                                    onsubmit="return confirm('Delete this announcement? This cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="announcement_id" value="<?= $a['announcement_id'] ?>">
                                    <button type="submit" class="action-delete"
                                        style="border:none; cursor:pointer; padding:6px 12px; border-radius:6px; font-size:0.82rem; font-weight:600;">
                                        Delete
                                    </button>
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
                        placeholder="Write the announcement details here..."
                        required style="min-height: 140px;"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ann_category">Category *</label>
                    <select id="ann_category" name="category" class="form-input" required>
                        <option value="" disabled selected>Select announcement category</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Safety">Safety</option>
                        <option value="Event">Event</option>
                        <option value="Health">Health</option>
                        <option value="Infrastructure">Infrastructure</option>
                        <option value="Notice">Notice</option>
                        <option value="Update">Update</option>
                        <option value="Emergency">Emergency</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Image <span class="form-label-optional">(Optional)</span>
                    </label>
                    <div class="file-upload-area" id="annFileUploadArea">
                        <div class="upload-icon">🖼️</div>
                        <div class="upload-text">Click to upload an image</div>
                        <div class="upload-hint">PNG, JPG, JPEG up to 5MB</div>
                        <div id="annPreviewContainer" class="preview-container">
                            <img src="" alt="Preview" class="preview-image" id="annPreviewImage">
                            <button type="button" class="remove-image-btn" id="annRemoveImageBtn">Remove Image</button>
                        </div>
                        <input type="file" name="image" id="annImageInput" class="file-input-hidden"
                            accept="image/png, image/jpeg, image/jpg">
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
    <script src="<?= ASSET_URL ?>/js/admin_module/announcement.js"></script>

    <script>
    // Briefly show loading state while the PDF is being generated
    document.getElementById('exportPdfBtn')?.addEventListener('click', function () {
        this.classList.add('loading');
        // Reset after a few seconds (in case user stays on the page)
        setTimeout(() => this.classList.remove('loading'), 4000);
    });
    </script>
</body>

</html>