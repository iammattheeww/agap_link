document.addEventListener("DOMContentLoaded", () => {
  // ── MEATBALLS MENU ────────────────────────────────────────────────────
  document.querySelectorAll(".meatballs-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      document
        .querySelectorAll(".meatballs-menu")
        .forEach((m) => (m.style.display = "none"));
      btn.nextElementSibling.style.display = "block";
    });
  });

  document.addEventListener("click", () => {
    document
      .querySelectorAll(".meatballs-menu")
      .forEach((m) => (m.style.display = "none"));
  });

  // ── MODAL ─────────────────────────────────────────────────────────────
  const modal = document.getElementById("reportModal");
  const closeModal = document.querySelector(".close-modal");
  const messageCitizenBtn = document.getElementById("messageCitizenBtn");

  let currentReportId = null;
  let currentReportPhone = null;
  let currentReportStatus = null; // ← FIX: was never declared — caused silent ReferenceError on every button click

  document.querySelectorAll(".view-details-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      currentReportId = btn.dataset.id;
      currentReportPhone = btn.dataset.phone || "";
      currentReportStatus = btn.dataset.status; // ← FIX: was never assigned — stayed null/undefined forever

      document.getElementById("modalTitle").textContent =
        "Report #" + currentReportId;
      document.getElementById("modalCategory").textContent =
        btn.dataset.category;
      document.getElementById("modalDescription").textContent =
        btn.dataset.description;
      document.getElementById("modalReporter").textContent =
        btn.dataset.reporter;
      document.getElementById("modalPhone").textContent =
        currentReportPhone || "N/A";
      document.getElementById("modalStatus").textContent = btn.dataset.status;
      document.getElementById("modalAgency").textContent =
        btn.dataset.agency || "—";
      document.getElementById("modalDate").textContent = btn.dataset.date;

      // PHOTO
      const photoWrapper = document.getElementById("modalPhotoWrapper");
      const photoEl = document.getElementById("modalPhoto");

      if (btn.dataset.photo && btn.dataset.photo !== "") {
        photoEl.src = btn.dataset.photo;
        photoWrapper.style.display = "block";
      } else {
        photoWrapper.style.display = "none";
        photoEl.src = "";
      }

      modal.style.display = "flex";
    });
  });

  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });

  // ── MESSAGE CITIZEN (PhilSMS — AUTO-GENERATED MESSAGE) ────────────────
  const STATUS_MESSAGES = {
    Pending: "AGAP-Link: Your report (#…) has been received.",
    Verified: "AGAP-Link: Your report (#…) has been verified.",
    Forwarded: "AGAP-Link: Your report (#…) was forwarded to authorities.",
    Ongoing: "AGAP-Link: Your report (#…) is being handled.",
    Resolved: "AGAP-Link: Your report (#…) has been resolved. Thank you!",
  };

  messageCitizenBtn.addEventListener("click", () => {
    if (!currentReportId || !currentReportStatus) {
      alert("❌ No report selected.");
      return;
    }

    const previewMessage = STATUS_MESSAGES[currentReportStatus]
      ? STATUS_MESSAGES[currentReportStatus].replace(/…/g, currentReportId)
      : "AGAP-Link: Your report (#" +
        currentReportId +
        ") status updated to " +
        currentReportStatus +
        ".";

    const confirmed = confirm(
      "📩 Send SMS Notification?\n\n" +
        "To      : " +
        (currentReportPhone || "citizen (no phone on record)") +
        "\n" +
        "Status  : " +
        currentReportStatus +
        "\n" +
        "Message : " +
        previewMessage +
        "\n\n" +
        "Click OK to send.",
    );

    if (!confirmed) return;

    const baseUrl = document.body.dataset.baseUrl || "";

    messageCitizenBtn.textContent = "Sending…";
    messageCitizenBtn.disabled = true;

    fetch(baseUrl + "/controller/send_sms.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        report_id: currentReportId,
        status: currentReportStatus,
      }),
    })
      .then((r) => {
        const ct = r.headers.get("content-type") || "";
        if (!ct.includes("application/json")) {
          throw new Error(
            "Server returned a non-JSON response (HTTP " +
              r.status +
              "). Check PHP error log.",
          );
        }
        return r.json();
      })
      .then((data) => {
        if (data.success) {
          alert(
            "✅ SMS sent successfully!\n\n" +
              "To      : " +
              (currentReportPhone || "citizen") +
              "\n" +
              "Message : " +
              previewMessage,
          );
        } else {
          alert("❌ Failed to send SMS: " + (data.error || "Unknown error."));
        }
      })
      .catch((err) => {
        console.error("[SMS]", err);
        alert("❌ Network or server error:\n" + err.message);
      })
      .finally(() => {
        messageCitizenBtn.textContent = "Message Citizen via SMS";
        messageCitizenBtn.disabled = false;
      });
  });

  // ── FORWARD MODAL ─────────────────────────────────────────────────────
  const forwardModal = document.getElementById("forwardModal");
  const forwardReportIdInput = document.getElementById("forwardReportId");

  window.showForwardModal = function (reportId) {
    forwardReportIdInput.value = reportId;
    forwardModal.style.display = "flex";
  };

  const closeForwardModal = document.getElementById("closeForwardModal");
  const cancelForwardModal = document.getElementById("cancelForwardModal");

  if (closeForwardModal) {
    closeForwardModal.addEventListener("click", () => {
      forwardModal.style.display = "none";
    });
  }
  if (cancelForwardModal) {
    cancelForwardModal.addEventListener("click", () => {
      forwardModal.style.display = "none";
    });
  }
  if (forwardModal) {
    forwardModal.addEventListener("click", (e) => {
      if (e.target === forwardModal) forwardModal.style.display = "none";
    });
  }
});
