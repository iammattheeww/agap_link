/**
 * AGAP-Link Landing Page JavaScript
 * Handles navigation, animations, and interactions
 */

(function () {
  "use strict";

  // ===================================
  // Mobile Navigation Toggle
  // ===================================
  const mobileToggle = document.getElementById("mobileToggle");
  const navMenu = document.getElementById("navMenu");

  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener("click", function () {
      this.classList.toggle("active");
      navMenu.classList.toggle("active");

      // Animate toggle bars
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

    // Close menu when clicking outside
    document.addEventListener("click", function (e) {
      if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
        mobileToggle.classList.remove("active");
        navMenu.classList.remove("active");
        const spans = mobileToggle.querySelectorAll("span");
        spans[0].style.transform = "";
        spans[1].style.opacity = "";
        spans[2].style.transform = "";
      }
    });
  }

  // ===================================
  // Smooth Scrolling for Navigation Links
  // ===================================
  const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");

      if (targetId === "#") return;

      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        const headerHeight =
          document.querySelector(".main-header").offsetHeight;
        const targetPosition = targetElement.offsetTop - headerHeight - 20;

        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        });

        // Close mobile menu if open
        if (navMenu.classList.contains("active")) {
          mobileToggle.click();
        }

        // Update active state
        navLinks.forEach((l) => l.classList.remove("active"));
        this.classList.add("active");
      }
    });
  });

  // ===================================
  // Header Scroll Effect
  // ===================================
  let lastScroll = 0;
  const header = document.querySelector(".main-header");

  window.addEventListener("scroll", function () {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      header.style.boxShadow = "0 2px 10px rgba(0, 0, 0, 0.1)";
      header.style.padding = "0.5rem 0";
    } else {
      header.style.boxShadow = "";
      header.style.padding = "1rem 0";
    }

    // Hide header on scroll down, show on scroll up
    if (currentScroll > lastScroll && currentScroll > 500) {
      header.style.transform = "translateY(-100%)";
    } else {
      header.style.transform = "translateY(0)";
    }

    lastScroll = currentScroll;
  });

  // ===================================
  // Scroll Animation Observer
  // ===================================
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  // Observe elements for animation
  const animatedElements = document.querySelectorAll(
    ".feature-card, .announcement-card",
  );
  animatedElements.forEach((el) => observer.observe(el));

  // ===================================
  // Parallax Effect for Hero Background
  // ===================================
  const heroBackground = document.querySelector(".hero-background");

  if (heroBackground) {
    window.addEventListener("scroll", function () {
      const scrolled = window.pageYOffset;
      const rate = scrolled * 0.5;

      if (scrolled < window.innerHeight) {
        heroBackground.style.transform = `translateY(${rate}px)`;
      }
    });
  }

  // ===================================
  // Counter Animation for Statistics (if needed)
  // ===================================
  function animateCounter(element, target, duration = 2000) {
    let current = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        element.textContent = target;
        clearInterval(timer);
      } else {
        element.textContent = Math.floor(current);
      }
    }, 16);
  }

  // ===================================
  // Form Validation (for future contact form)
  // ===================================
  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  function validateForm(formElement) {
    const inputs = formElement.querySelectorAll(
      "input[required], textarea[required]",
    );
    let isValid = true;

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        isValid = false;
        input.classList.add("error");
      } else {
        input.classList.remove("error");
      }

      if (input.type === "email" && !validateEmail(input.value)) {
        isValid = false;
        input.classList.add("error");
      }
    });

    return isValid;
  }

  // ===================================
  // Add Hover Effect to Cards
  // ===================================
  const cards = document.querySelectorAll(".feature-card, .announcement-card");

  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
    });
  });

  // ===================================
  // Active Navigation Highlight on Scroll
  // ===================================
  window.addEventListener("scroll", function () {
    const sections = document.querySelectorAll("section[id]");
    const scrollPosition = window.pageYOffset + 200;

    sections.forEach((section) => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      const sectionId = section.getAttribute("id");

      if (
        scrollPosition >= sectionTop &&
        scrollPosition < sectionTop + sectionHeight
      ) {
        navLinks.forEach((link) => {
          link.classList.remove("active");
          if (link.getAttribute("href") === `#${sectionId}`) {
            link.classList.add("active");
          }
        });
      }
    });
  });

  // ===================================
  // Lazy Loading Images (future enhancement)
  // ===================================
  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.add("loaded");
          observer.unobserve(img);
        }
      });
    });

    const images = document.querySelectorAll("img[data-src]");
    images.forEach((img) => imageObserver.observe(img));
  }

  // ===================================
  // Add Loading State to Buttons
  // ===================================
  function addLoadingState(button) {
    button.classList.add("loading");
    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = "Loading...";

    return function removeLoadingState() {
      button.classList.remove("loading");
      button.disabled = false;
      button.textContent = originalText;
    };
  }

  // ===================================
  // Toast Notification System (future use)
  // ===================================
  function showToast(message, type = "info", duration = 3000) {
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add("show");
    }, 100);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, duration);
  }

  // ===================================
  // Performance Optimization
  // ===================================
  // Debounce function for scroll events
  function debounce(func, wait = 20) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Throttle function for resize events
  function throttle(func, limit = 100) {
    let inThrottle;
    return function (...args) {
      if (!inThrottle) {
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }

  // ===================================
  // Initialize on DOM Ready
  // ===================================
  document.addEventListener("DOMContentLoaded", function () {
    console.log("AGAP-Link initialized successfully!");

    // Add fade-in animation to hero content
    const heroContent = document.querySelector(".hero-content");
    if (heroContent) {
      setTimeout(() => {
        heroContent.style.opacity = "1";
      }, 100);
    }

    // Initialize any third-party libraries here
  });

  // ===================================
  // Export functions for external use
  // ===================================
  window.AGAPLink = {
    showToast,
    validateForm,
    addLoadingState,
    debounce,
    throttle,
  };
})();
