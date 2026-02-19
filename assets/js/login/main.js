const container = document.getElementById("authContainer");
const loginTab = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");

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

function showTab(tab) {
  document
    .querySelectorAll(".tab-content")
    .forEach((el) => el.classList.remove("active"));
  document
    .querySelectorAll(".tab-btn")
    .forEach((el) => el.classList.remove("active"));

  document.getElementById(tab).classList.add("active");
  event.target.classList.add("active");
}

const miInput = document.querySelector('input[name="middle_initial"]');

miInput.addEventListener('input', () => {
  miInput.value = miInput.value.toUpperCase().slice(0, 1); // Uppercase and limit to 1 char
});
