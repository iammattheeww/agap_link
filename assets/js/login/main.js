// ── Tab Switching ──────────────────────────────────────
const loginTab    = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");
// const agencyTab   = document.getElementById("agencyTab");
const container   = document.getElementById("authContainer");

function showTab(tabId) {
  document.querySelectorAll(".tab-content").forEach(el => el.classList.remove("active"));
  document.querySelectorAll(".tab-btn").forEach(el => el.classList.remove("active"));
  document.getElementById(tabId).classList.add("active");
  if (tabId === "register") {
    registerTab.classList.add("active");
    container && container.classList.add("signup-active");
  } else if (tabId === "agency") {
    agencyTab && agencyTab.classList.add("active");
    container && container.classList.remove("signup-active");
  } else {
    loginTab.classList.add("active");
    container && container.classList.remove("signup-active");
  }
}

if (loginTab)    loginTab.addEventListener("click",    () => showTab("login"));
if (registerTab) registerTab.addEventListener("click", () => showTab("register"));
// if (agencyTab)   agencyTab.addEventListener("click",   () => showTab("agency"));

// ── Middle Initial — uppercase & 1 char ────────────────
const miInput = document.querySelector('input[name="middle_initial"]');
if (miInput) {
  miInput.addEventListener("input", () => {
    miInput.value = miInput.value.toUpperCase().slice(0, 1);
  });
}

// ── Password Toggle ────────────────────────────────────
function togglePassword(fieldId, iconWrapper) {
  const input = document.getElementById(fieldId);
  const icon  = iconWrapper.querySelector("i");
  if (!input) return;
  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.replace("fa-eye-slash", "fa-eye");
  }
}

// ── Terms Modal ────────────────────────────────────────
function openTermsModal()  { document.getElementById("termsModal").style.display = "block"; }
function closeTermsModal() { document.getElementById("termsModal").style.display = "none";  }
window.onclick = function(event) {
  const modal = document.getElementById("termsModal");
  if (modal && event.target === modal) modal.style.display = "none";
};

// ── On Load: activate correct tab + auto-fade toast ───
document.addEventListener("DOMContentLoaded", function () {
  // activeTab is injected by the PHP page inline (see index.php)
  const activeTab = window.__activeTab || "login";
  showTab(activeTab);

  const toast = document.querySelector(".toast");
  if (toast) {
    setTimeout(() => toast.classList.remove("show"), 3500);
  }
});
