document.addEventListener("DOMContentLoaded", function () {

  // ── Report Modal ──────────────────────────────────────
  const modal    = document.getElementById("reportModal");
  const openBtn  = document.getElementById("openReportModal");
  const closeBtn = document.getElementById("closeReportModal");
  const cancelBtn = document.getElementById("cancelReportBtn");

  function openModal()  { if (modal) modal.classList.add("active"); }
  function closeModal() { if (modal) modal.classList.remove("active"); }

  if (openBtn)   openBtn.addEventListener("click",  openModal);
  if (closeBtn)  closeBtn.addEventListener("click",  closeModal);
  if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeModal();
    });
  }

  // ── Geolocation ───────────────────────────────────────
  const getLocationBtn = document.getElementById("getLocationBtn");
  const locationStatus = document.getElementById("locationStatus");
  const latInput       = document.getElementById("gps_lat");
  const longInput      = document.getElementById("gps_long");

  if (getLocationBtn) {
    getLocationBtn.addEventListener("click", function () {
      if (!navigator.geolocation) {
        if (locationStatus) locationStatus.innerHTML = "Geolocation is not supported.";
        return;
      }
      if (locationStatus) locationStatus.innerHTML = "Getting location...";
      navigator.geolocation.getCurrentPosition(
        function (position) {
          if (latInput)  latInput.value  = position.coords.latitude;
          if (longInput) longInput.value = position.coords.longitude;
          if (locationStatus) locationStatus.innerHTML = "✅ Location captured successfully.";
        },
        function () {
          if (locationStatus) locationStatus.innerHTML = "❌ Unable to retrieve location.";
        }
      );
    });
  }

  // ── File Upload Preview ───────────────────────────────
  const fileUploadArea   = document.getElementById("fileUploadArea");
  const fileInput        = document.getElementById("photo");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage     = document.getElementById("previewImage");
  const removeBtn        = document.getElementById("removeImageBtn");

  if (fileUploadArea && fileInput) {
    fileUploadArea.addEventListener("click", function () { fileInput.click(); });
  }
  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        if (previewImage)     previewImage.src = e.target.result;
        if (previewContainer) previewContainer.style.display = "block";
      };
      reader.readAsDataURL(file);
    });
  }
  if (removeBtn) {
    removeBtn.addEventListener("click", function () {
      if (fileInput)        fileInput.value = "";
      if (previewContainer) previewContainer.style.display = "none";
    });
  }

  // ── Init Leaflet map when modal opens ─────────────────
  if (openBtn) {
    openBtn.addEventListener("click", function () {
      setTimeout(() => {
        if (typeof initReportMap === "function") initReportMap();
      }, 150);
    });
  }

});
