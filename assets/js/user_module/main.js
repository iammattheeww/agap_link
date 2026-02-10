document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // MOBILE MENU TOGGLE FUNCTIONALITY
  // ============================================

  const initMobileMenu = () => {
    const mobileBtn = document.querySelector(".mobile-menu-toggle");
    const sidebar = document.querySelector(".sidebar");

    // Only proceed if both elements exist
    if (!mobileBtn || !sidebar) {
      return;
    }

    // Toggle sidebar on button click
    mobileBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("sidebar-open");

      // Change icon based on state
      if (sidebar.classList.contains("sidebar-open")) {
        this.innerHTML = "✕";
      } else {
        this.innerHTML = "☰";
      }
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", function (e) {
      if (sidebar.classList.contains("sidebar-open")) {
        if (!sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
          sidebar.classList.remove("sidebar-open");
          mobileBtn.innerHTML = "☰";
        }
      }
    });

    // Close sidebar when clicking on nav items (for better UX on mobile)
    const navItems = sidebar.querySelectorAll(".nav-item");
    navItems.forEach((item) => {
      item.addEventListener("click", function () {
        if (window.innerWidth <= 480) {
          sidebar.classList.remove("sidebar-open");
          mobileBtn.innerHTML = "☰";
        }
      });
    });
  };

  // Initialize mobile menu
  initMobileMenu();

  // ============================================
  // SMOOTH SCROLLING FOR ANCHOR LINKS
  // ============================================
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
});
