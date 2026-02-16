const container = document.getElementById("authContainer");
const loginTab = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");

if (loginTab && registerTab && container) {
  loginTab.addEventListener("click", () => {
    container.classList.remove("signup-active");
    loginTab.classList.add("active");
    registerTab.classList.remove("active");
  });

  registerTab.addEventListener("click", () => {
    container.classList.add("signup-active");
    registerTab.classList.add("active");
    loginTab.classList.remove("active");
  });
}

function showTab(tab) {
  document
    .querySelectorAll(".tab-content")
    .forEach((el) => el.classList.remove("active"));
  document
    .querySelectorAll(".tab-btn")
    .forEach((el) => el.classList.remove("active"));

  document.getElementById(tab).classList.add("active");

  if (window.event && window.event.target) {
    window.event.target.classList.add("active");
  }
}

// AUTO-HIDE ALERTS AND SUCCESS/ERROR MESSAGES
document.addEventListener("DOMContentLoaded", () => {
  // Target both the login page (.success, .error) and landing page (.alert) messages
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
