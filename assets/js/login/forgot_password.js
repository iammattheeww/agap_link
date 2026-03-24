/* ── forgot_password.js ───────────────────────────────────────────────────── */

const baseUrl = window.__baseUrl || "";

let _forgotEmail = "";
let _forgotMaskedPhone = "";
let _forgotMaskedEmail = "";
let _otpTimerInterval = null;

function openForgotModal() {
  document.getElementById("forgotModal").classList.add("active");
  document.getElementById("forgotEmail").value = "";
  goToForgotStep(1);
}

function closeForgotModal() {
  document.getElementById("forgotModal").classList.remove("active");
  clearOtpTimer();
}

function goToForgotStep(n) {
  for (let i = 1; i <= 3; i++) {
    const step = document.getElementById("forgotStep" + i);
    const dot = document.getElementById("dot" + i);
    if (step) step.classList.remove("active");
    if (dot) dot.classList.remove("active");
  }

  const activeStep = document.getElementById("forgotStep" + n);
  const activeDot = document.getElementById("dot" + n);
  if (activeStep) activeStep.classList.add("active");
  if (activeDot) activeDot.classList.add("active");

  [
    "forgotStep1Error",
    "forgotStep2Error",
    "forgotStep3Error",
    "forgotStep3Success",
  ].forEach(function (id) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = "";
      el.style.display = "none";
    }
  });
}

function forgotFindUser() {
  const emailInput = document.getElementById("forgotEmail");
  const btn = document.getElementById("forgotStep1Btn");
  const errEl = document.getElementById("forgotStep1Error");
  const email = emailInput.value.trim();

  btn.disabled = true;

  const fd = new FormData();
  fd.append("email", email);

  fetch(baseUrl + "/controller/forgot_password_process.php?action=find_user", {
    method: "POST",
    body: fd,
  })
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (data.success) {
        _forgotEmail = data.email;
        _forgotMaskedPhone = data.masked_phone;
        _forgotMaskedEmail = data.masked_email;

        const phoneDisplay = document.getElementById("maskedPhoneDisplay");
        const emailDisplay = document.getElementById("maskedEmailDisplay");
        if (phoneDisplay) phoneDisplay.textContent = _forgotMaskedPhone;
        if (emailDisplay) emailDisplay.textContent = _forgotMaskedEmail;

        goToForgotStep(2);
      } else {
        errEl.textContent = data.message || "Something went wrong.";
        errEl.style.display = "block";
      }
    })
    .catch(function () {
      errEl.textContent = "Network error. Please try again.";
      errEl.style.display = "block";
    })
    .finally(function () {
      btn.disabled = false;
    });
}

function forgotSendOtp(channel) {
  const errEl = document.getElementById("forgotStep2Error");
  const btnSms = document.getElementById("btnSendSms");
  const btnEml = document.getElementById("btnSendEmail");

  btnSms.disabled = true;
  btnEml.disabled = true;

  const fd = new FormData();
  fd.append("email", _forgotEmail);
  fd.append("channel", channel);

  fetch(baseUrl + "/controller/forgot_password_process.php?action=send_otp", {
    method: "POST",
    body: fd,
  })
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (data.success) {
        goToForgotStep(3);
        startOtpTimer();

        const otpField = document.getElementById("forgotOtp");
        const newPass = document.getElementById("forgotNewPass");
        const confPass = document.getElementById("forgotConfPass");
        if (otpField) otpField.value = "";
        if (newPass) newPass.value = "";
        if (confPass) confPass.value = "";
      } else {
        errEl.textContent = data.message || "Failed to send code.";
        errEl.style.display = "block";
      }
    })
    .catch(function () {
      errEl.textContent = "Network error. Please try again.";
      errEl.style.display = "block";
    })
    .finally(function () {
      btnSms.disabled = false;
      btnEml.disabled = false;
    });
}

function forgotResetPassword() {
  const otpVal = (document.getElementById("forgotOtp").value || "").trim();
  const newPass = document.getElementById("forgotNewPass").value || "";
  const confPass = document.getElementById("forgotConfPass").value || "";
  const errEl = document.getElementById("forgotStep3Error");
  const successEl = document.getElementById("forgotStep3Success");
  const btn = document.getElementById("forgotStep3Btn");

  if (otpVal.length !== 6 || !/^\d{6}$/.test(otpVal)) {
    errEl.textContent = "Please enter the 6-digit code.";
    errEl.style.display = "block";
    return;
  }

  if (newPass.length < 8) {
    errEl.textContent = "Password must be at least 8 characters long.";
    errEl.style.display = "block";
    return;
  }

  if (newPass !== confPass) {
    errEl.textContent = "Passwords do not match.";
    errEl.style.display = "block";
    return;
  }

  errEl.textContent = "";
  errEl.style.display = "none";
  btn.disabled = true;

  const fd = new FormData();
  fd.append("email", _forgotEmail);
  fd.append("otp_code", otpVal);
  fd.append("new_password", newPass);
  fd.append("confirm_password", confPass);

  fetch(
    baseUrl + "/controller/forgot_password_process.php?action=reset_password",
    {
      method: "POST",
      body: fd,
    },
  )
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (data.success) {
        clearOtpTimer();
        successEl.textContent =
          (data.message || "Password reset successfully.") +
          " Closing in 3 seconds…";
        successEl.style.display = "block";
        setTimeout(function () {
          window.location.reload();
        }, 3000);
      } else {
        errEl.textContent = data.message || "Failed to reset password.";
        errEl.style.display = "block";
        btn.disabled = false;
      }
    })
    .catch(function () {
      errEl.textContent = "Network error. Please try again.";
      errEl.style.display = "block";
      btn.disabled = false;
    });
}

function startOtpTimer() {
  clearOtpTimer();
  const timerEl = document.getElementById("otpTimer");
  let remaining = 300;

  function tick() {
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    timerEl.textContent = mins + ":" + String(secs).padStart(2, "0");

    if (remaining <= 0) {
      timerEl.textContent = "Expired";
      timerEl.classList.add("expired");
      return;
    }

    remaining--;
    _otpTimerInterval = setTimeout(tick, 1000);
  }

  tick();
}

function clearOtpTimer() {
  if (_otpTimerInterval !== null) {
    clearTimeout(_otpTimerInterval);
    _otpTimerInterval = null;
  }
}

document
  .getElementById("forgotPasswordLink")
  .addEventListener("click", function () {
    openForgotModal();
  });

document
  .getElementById("forgotCloseBtn")
  .addEventListener("click", function () {
    closeForgotModal();
  });

document.getElementById("forgotModal").addEventListener("click", function (e) {
  if (e.target === document.getElementById("forgotModal")) {
    closeForgotModal();
  }
});

document
  .getElementById("forgotStep1Btn")
  .addEventListener("click", function () {
    forgotFindUser();
  });

document
  .getElementById("forgotEmail")
  .addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      forgotFindUser();
    }
  });

document.getElementById("btnSendSms").addEventListener("click", function () {
  forgotSendOtp("sms");
});

document.getElementById("btnSendEmail").addEventListener("click", function () {
  forgotSendOtp("email");
});

document
  .getElementById("forgotStep3Btn")
  .addEventListener("click", function () {
    forgotResetPassword();
  });

document
  .getElementById("forgotResendLink")
  .addEventListener("click", function () {
    clearOtpTimer();
    goToForgotStep(2);
  });

document.getElementById("forgotOtp").addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "").slice(0, 6);
});
