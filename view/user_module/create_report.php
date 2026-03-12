<?php
require_once dirname(__DIR__, 2) . "/config/init.php";

// PREVENT BROWSER CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

require_once MODEL_PATH . 'Report.php';

// FETCH CATEGORIES USING MODEL (MVC Compliant)
$reportModel = new Report();
$categories = $reportModel->getAllCategories();

// GET USER NAME FROM SESSION
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= ASSET_URL ?>/favicon_io/favicon.ico">
    <title>Create Report - AGAP-Link</title>
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/user_module/user_module.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once VIEW_PATH . 'partials/user_sidebar.php'; ?>

        <main class="main-content">
            <div class="create-report-container">
                <div class="page-header">
                    <h1 class="page-title">Create New Report</h1>
                    <p class="page-description">
                        Help improve your community by reporting issues. Provide as much detail as possible to help us address the problem quickly.
                    </p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Report submitted successfully!
                    </div>
                <?php endif; ?>

                <div class="report-form-section">
                    <form action="<?= BASE_URL ?>/controller/create_report_process.php" method="POST" enctype="multipart/form-data" id="createReportForm">
                        <input type="hidden" name="action" value="create_report">

                        <div class="form-group">
                            <label class="form-label" for="category_id">Report Category *</label>
                            <select name="category_id" id="category_id" class="form-input" required>
                                <option value="">Select a category...</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $row): ?>
                                        <option value="<?= $row['category_id']; ?>">
                                            <?= htmlspecialchars($row['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No categories available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="description">Description *</label>
                            <textarea
                                name="description"
                                id="description"
                                class="form-input"
                                placeholder="Please describe the issue in detail..."
                                required
                                maxlength="1000"></textarea>
                            <small class="form-label-optional">Maximum 1000 characters</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address">Address/Location *</label>
                            <input
                                type="text"
                                name="address"
                                id="address"
                                class="form-input"
                                placeholder="Street, Barangay, City"
                                required
                                maxlength="255">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Photo Evidence <span class="form-label-optional">(Optional)</span></label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <div class="upload-icon">📷</div>
                                    <div class="upload-text">Click to upload or drag and drop</div>
                                    <div class="upload-hint">PNG, JPG, JPEG up to 5MB</div>
                                </div>
                                <input
                                    type="file"
                                    name="photo"
                                    id="photo"
                                    class="file-input-hidden"
                                    accept="image/png, image/jpeg, image/jpg">
                                <div class="preview-container" id="previewContainer">
                                    <img src="" alt="Preview" class="preview-image" id="previewImage">
                                    <button type="button" class="remove-image-btn" id="removeImageBtn">Remove Photo</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GPS Coordinates <span class="form-label-optional">(Optional)</span></label>
                            <button type="button" class="btn-get-location" id="getLocationBtn">
                                <span>📍</span>
                                Get My Current Location
                            </button>
                            <div id="locationStatus"></div>
                            <input type="hidden" name="gps_lat" id="gps_lat">
                            <input type="hidden" name="gps_long" id="gps_long">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Submit Report</button>
                            <a href="<?= BASE_URL ?>/view/user_module/user_dashboard.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= ASSET_URL ?>/js/user_module/create_report.js"></script>
    <script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>
    <button class="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
</body>

</html>