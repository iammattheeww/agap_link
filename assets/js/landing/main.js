document.addEventListener("DOMContentLoaded", () => {
  // ==========================================
  // 1. MOBILE HAMBURGER MENU TOGGLE
  // ==========================================
  const mobileToggle = document.getElementById("mobileToggle");
  const navMenu = document.getElementById("navMenu");

  if (mobileToggle && navMenu) {
    // Toggle menu when clicking the hamburger icon
    mobileToggle.addEventListener("click", function (e) {
      e.stopPropagation(); // Prevent the click from bubbling up to the document
      this.classList.toggle("active");
      navMenu.classList.toggle("active");

      // Animate the three span lines into an "X"
      const spans = this.querySelectorAll("span");
      if (this.classList.contains("active")) {
        spans[0].style.transform = "rotate(45deg) translateY(8px)";
        spans[1].style.opacity = "0";
        spans[2].style.transform = "rotate(-45deg) translateY(-8px)";
      } else {
        spans[0].style.transform = "";
        spans[1].style.opacity = "";
        spans[2].style.transform = "";
      }
    });

    // Close menu when clicking anywhere outside of it
    document.addEventListener("click", function (e) {
      if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
        mobileToggle.classList.remove("active");
        navMenu.classList.remove("active");

        // Reset the hamburger lines
        const spans = mobileToggle.querySelectorAll("span");
        if (spans.length === 3) {
          spans[0].style.transform = "";
          spans[1].style.opacity = "";
          spans[2].style.transform = "";
        }
      }
    });

    // Close menu when clicking any navigation link inside it
    const navLinks = navMenu.querySelectorAll(".nav-link, .btn");
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        mobileToggle.classList.remove("active");
        navMenu.classList.remove("active");

        // Reset the hamburger lines
        const spans = mobileToggle.querySelectorAll("span");
        if (spans.length === 3) {
          spans[0].style.transform = "";
          spans[1].style.opacity = "";
          spans[2].style.transform = "";
        }
      });
    });
  }

  // ==========================================
  // 2. AUTO-HIDE FLASH MESSAGES
  // ==========================================
  const flashMessages = document.querySelectorAll(".alert, .success, .error");

  flashMessages.forEach((message) => {
    // Wait 3.5 seconds before starting the fade out
    setTimeout(() => {
      message.style.transition = "opacity 0.5s ease-out";
      message.style.opacity = "0";

      // Remove the element from the DOM after the fade transition completes
      setTimeout(() => {
        if (message.parentNode) {
          message.parentNode.removeChild(message);
        }
      }, 500);
    }, 3500);
  });
});
