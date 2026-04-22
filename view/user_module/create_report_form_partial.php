<?php
// FETCH CATEGORIES EXCLUDING "Other" (category_id = 10)
if (!class_exists('Report')) {
    require_once MODEL_PATH . 'Report.php';
}
$_reportModelPartial = new Report();
$categories = array_filter(
    $_reportModelPartial->getAllCategories(),
    fn($cat) => strtolower($cat['name']) !== 'other' && $cat['category_id'] != 10
);

$userName = $_SESSION['user_name'] ?? 'User';
?>
<div class="report-form-section">
    <form action="<?= BASE_URL ?>/controller/create_report_process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_report">

        <div class="form-group">
            <label class="form-label" for="category_id">Report Category *</label>
            <select name="category_id" id="category_id" class="form-input" required>
                <option value="">Select a category...</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= $row['category_id'] ?>">
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description *</label>
            <textarea name="description" id="description" class="form-input"
                placeholder="Please describe the issue in detail..."
                required maxlength="1000"></textarea>
            <small class="form-label-optional">Maximum 1000 characters</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Address/Location *</label>
            <input type="text" name="address" id="address" class="form-input"
                placeholder="Street, Barangay, City" required maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label">Photo Evidence <span class="form-label-optional">(Optional)</span></label>

            <!-- THE FIX: label wraps the upload area and is linked to the input -->
            <!-- Clicking anywhere on the label triggers the file input natively -->
            <input type="file" name="photo" id="photo"
                style="display:none;"
                accept="image/png, image/jpeg, image/jpg">

            <label for="photo" class="file-upload-area" id="fileUploadArea">

                <!-- PLACEHOLDER -->
                <div class="upload-placeholder" id="uploadPlaceholder">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">Click to upload or drag and drop</div>
                    <div class="upload-hint">PNG, JPG, JPEG up to 5MB</div>
                </div>

                <!-- PREVIEW -->
                <div class="preview-container" id="previewContainer">
                    <img src="" alt="Preview" class="preview-image" id="previewImage">
                </div>

            </label>

            <!-- REMOVE BUTTON OUTSIDE THE LABEL SO IT DOESN'T TRIGGER FILE DIALOG -->
            <button type="button" class="remove-image-btn" id="removeImageBtn" style="display:none;">
                ✕ Remove Photo
            </button>

        </div>

        <div class="form-group">
            <label class="form-label">
                Pin Exact Location on Map
                <span class="form-label-optional">(Optional)</span>
            </label>

            <div class="form-group">
                <label class="form-label">Report Location</label>
                <div id="reportMap" class="report-map"></div>
            </div>

            <small class="form-label-optional">
                Tap or drag the marker to set the exact report location.
            </small>

            <input type="hidden" name="gps_lat" id="gps_lat">
            <input type="hidden" name="gps_long" id="gps_long">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Submit Report</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof initReportMap === "function") {
            initReportMap();
        }
    });
</script>
<!-- CORRECT ORDER -->
<script src="<?= ASSET_URL ?>/js/user_module/create_report.js"></script>
<script src="<?= ASSET_URL ?>/js/user_module/main.js"></script>