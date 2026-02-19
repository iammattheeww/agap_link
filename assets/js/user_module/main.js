document.addEventListener("DOMContentLoaded", function () {

  // ============================================
  // MOBILE TOP NAV + SLIDE-IN SIDEBAR
  // ============================================
  const hamburger = document.querySelector(".topnav-hamburger");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".mobile-overlay");

  if (hamburger && sidebar) {
    hamburger.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("sidebar-open");
      overlay && overlay.classList.toggle("active");
      this.textContent = sidebar.classList.contains("sidebar-open") ? "✕" : "☰";
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      sidebar && sidebar.classList.remove("sidebar-open");
      overlay.classList.remove("active");
      if (hamburger) hamburger.textContent = "☰";
    });
  }

  // Also support old mobile-menu-toggle button (fallback)
  const oldToggle = document.querySelector(".mobile-menu-toggle");
  if (oldToggle && sidebar) {
    oldToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("sidebar-open");
      overlay && overlay.classList.toggle("active");
      this.textContent = sidebar.classList.contains("sidebar-open") ? "✕" : "☰";
    });
  }

  // Close sidebar on nav item click (mobile)
  if (sidebar) {
    sidebar.querySelectorAll(".nav-item").forEach(function (item) {
      item.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove("sidebar-open");
          overlay && overlay.classList.remove("active");
          if (hamburger) hamburger.textContent = "☰";
          if (oldToggle) oldToggle.textContent = "☰";
        }
      });
    });
  }

  // ============================================
  // REPORT MODAL (Dashboard)
  // ============================================
  const reportModal = document.getElementById("reportModal");
  const openBtn = document.getElementById("openReportModal");
  const closeBtn = document.getElementById("closeReportModal");
  const cancelBtn = document.getElementById("cancelReportBtn");

  if (openBtn && reportModal) {
    openBtn.addEventListener("click", function () {
      reportModal.classList.add("active");
    });
  }

  if (closeBtn && reportModal) {
    closeBtn.addEventListener("click", function () {
      reportModal.classList.remove("active");
    });
  }

  if (cancelBtn && reportModal) {
    cancelBtn.addEventListener("click", function () {
      reportModal.classList.remove("active");
    });
  }

  if (reportModal) {
    reportModal.addEventListener("click", function (e) {
      if (e.target === reportModal) {
        reportModal.classList.remove("active");
      }
    });
  }

  // ============================================
  // GEOLOCATION (Report Form)
  // ============================================
  const getLocationBtn = document.getElementById("getLocationBtn");
  const locationStatus = document.getElementById("locationStatus");
  const latInput = document.getElementById("gps_lat");
  const longInput = document.getElementById("gps_long");

  if (getLocationBtn) {
    getLocationBtn.addEventListener("click", function () {
      if (!navigator.geolocation) {
        if (locationStatus) locationStatus.innerHTML = "Geolocation is not supported.";
        return;
      }
      if (locationStatus) locationStatus.innerHTML = "Getting location...";
      navigator.geolocation.getCurrentPosition(
        function (position) {
          if (latInput) latInput.value = position.coords.latitude;
          if (longInput) longInput.value = position.coords.longitude;
          if (locationStatus) locationStatus.innerHTML = "Location captured successfully.";
        },
        function () {
          if (locationStatus) locationStatus.innerHTML = "Unable to retrieve location.";
        }
      );
    });
  }

  // ============================================
  // FILE UPLOAD PREVIEW (Report Form)
  // ============================================
  const fileUploadArea = document.getElementById("fileUploadArea");
  const fileInput = document.getElementById("photo");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeBtn = document.getElementById("removeImageBtn");

  if (fileUploadArea && fileInput) {
    fileUploadArea.addEventListener("click", function () {
      fileInput.click();
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        if (previewImage) previewImage.src = e.target.result;
        if (previewContainer) previewContainer.style.display = "block";
      };
      reader.readAsDataURL(file);
    });
  }

  if (removeBtn) {
    removeBtn.addEventListener("click", function () {
      if (fileInput) fileInput.value = "";
      if (previewContainer) previewContainer.style.display = "none";
    });
  }

  // ============================================
  // SMOOTH SCROLLING
  // ============================================
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });
});
