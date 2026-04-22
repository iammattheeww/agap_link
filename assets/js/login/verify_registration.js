document.addEventListener("DOMContentLoaded", function () {
  const otpInput = document.getElementById("otpCode");
  const countdownTimer = document.getElementById("countdownTimer");

  // ━━━ AUTO-FOCUS & FORMAT OTP INPUT ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  if (otpInput) {
    otpInput.addEventListener("input", function () {
      // Remove non-digits
      this.value = this.value.replace(/[^\d]/g, "");
      // Limit to 6 digits
      if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
      }
    });

    // Auto-focus: when user pastes code, auto-fill
    otpInput.addEventListener("paste", function (e) {
      e.preventDefault();
      let pastedText = (e.clipboardData || window.clipboardData).getData(
        "text",
      );
      pastedText = pastedText.replace(/[^\d]/g, "").slice(0, 6);
      this.value = pastedText;
    });
  }

  // ━━━ 5-MINUTE COUNTDOWN TIMER ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  let timeRemaining = 5 * 60; // 5 minutes in seconds

  function updateCountdown() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    const display = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
    countdownTimer.textContent = display;

    if (timeRemaining <= 0) {
      countdownTimer.classList.add("expired");
      countdownTimer.textContent = "Code Expired";
      otpInput.disabled = true;
    } else {
      timeRemaining--;
      setTimeout(updateCountdown, 1000);
    }
  }

  updateCountdown();
});
