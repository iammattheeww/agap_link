/* ── verify_login.js ──────────────────────────────────────────────────────── */

(function () {
  const timerEl = document.getElementById("countdownTimer");
  let totalSecs = 5 * 60;

  function tick() {
    const mins = Math.floor(totalSecs / 60);
    const secs = totalSecs % 60;
    timerEl.textContent = mins + ":" + String(secs).padStart(2, "0");

    if (totalSecs <= 0) {
      timerEl.textContent = "Expired";
      timerEl.classList.add("expired");
      return;
    }

    totalSecs--;
    setTimeout(tick, 1000);
  }

  tick();
})();

document.getElementById("tokenCode").addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "").slice(0, 6);
});
