document.addEventListener("DOMContentLoaded", function () {
  // // ============================================
  // // ACTIVE SIDEBAR STATE DETECTION
  // // ============================================
  // const currentPath = window.location.pathname;
  // const navItems = document.querySelectorAll(".nav-item");

  // navItems.forEach((item) => {
  //   // Remove active class from all items first
  //   item.classList.remove("active");

  //   // Get the href attribute
  //   const href = item.getAttribute("href");

  //   // Check if current path matches the nav item
  //   if (currentPath.includes(href)) {
  //     item.classList.add("active");
  //   }
  // });

  // MOBILE MENU TOGGLE
  const createMobileMenuButton = () => {
    // Check if button already exists
    if (document.querySelector(".mobile-menu-toggle")) {
      return;
    }

  // Create mobile menu toggle button
    const mobileBtn = document.createElement("button");
    mobileBtn.className = "mobile-menu-toggle";
    mobileBtn.innerHTML = "☰";
    mobileBtn.setAttribute("aria-label", "Toggle Menu");

    // Append to body
    document.body.appendChild(mobileBtn);

    // Get sidebar element
    const sidebar = document.querySelector(".sidebar");

    // Toggle sidebar on button click
    mobileBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("sidebar-open");

      // Change icon
      if (sidebar.classList.contains("sidebar-open")) {
        this.innerHTML = "✕";
      } else {
        this.innerHTML = "☰";
      }
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", function (e) {
      if (sidebar && sidebar.classList.contains("sidebar-open")) {
        if (!sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
          sidebar.classList.remove("sidebar-open");
          mobileBtn.innerHTML = "☰";
        }
      }
    });
  };

  // Create mobile menu button on mobile devices
  if (window.innerWidth <= 480) {
    createMobileMenuButton();
  }

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth <= 480) {
      createMobileMenuButton();
    } else {
      const mobileBtn = document.querySelector(".mobile-menu-toggle");
      if (mobileBtn) {
        mobileBtn.remove();
      }
      const sidebar = document.querySelector(".sidebar");
      if (sidebar) {
        sidebar.classList.remove("sidebar-open");
      }
    }
  });

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
