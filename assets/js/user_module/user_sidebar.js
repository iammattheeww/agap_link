// THIS SCRIPT IS FOR THE ACTIVE SIDEBAR NAV ITEM INDICATOR AND MOBILE MENU TOGGLE BUTTON
lucide.createIcons();

let currentY = 0;

function moveIndicator() {
  const active = document.querySelector(".sidebar-nav .nav-item.active");
  const indicator = document.querySelector(".active-indicator");

  if (!active || !indicator) return;

  const newY = active.offsetTop;

  indicator.style.height = active.offsetHeight + "px";
  indicator.style.transform = `translateY(${newY}px)`;

  currentY = newY;
}

window.addEventListener("DOMContentLoaded", () => {
  moveIndicator();

  // slight delay gives visible animation on page load
  setTimeout(moveIndicator, 50);
});

window.addEventListener("resize", moveIndicator);
