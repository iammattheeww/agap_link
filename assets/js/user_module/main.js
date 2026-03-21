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
      this.textContent = sidebar.classList.contains("sidebar-open")
        ? "✕"
        : "☰";
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
      this.textContent = sidebar.classList.contains("sidebar-open")
        ? "✕"
        : "☰";
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
