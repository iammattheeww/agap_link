/* ============================================================
   admin_reports.js – AGAP-Link Admin Reports Module
   Handles: meatball menus, view-details modal, forward modal,
            SMS notifications, and Excel export.
   ============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  // ── CONFIG & STATE ──────────────────────────────────────────
  const baseUrl = document.body.dataset.baseUrl || "";
  let currentReportId = null;
  let currentReportPhone = null;
  let currentReportStatus = null;

  // ── MEATBALLS MENU ──────────────────────────────────────────
  document.querySelectorAll(".meatballs-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      // Close all other open menus first
      document.querySelectorAll(".meatballs-menu.open").forEach((m) => {
        if (m !== this.nextElementSibling) m.classList.remove("open");
      });
      this.nextElementSibling.classList.toggle("open");
    });
  });

  // Close meatballs when clicking outside
  document.addEventListener("click", function () {
    document
      .querySelectorAll(".meatballs-menu.open")
      .forEach((m) => m.classList.remove("open"));
  });

  // ── MODAL LOGIC (VIEW DETAILS) ──────────────────────────────
  const reportModal = document.getElementById("reportModal");
  const forwardModal = document.getElementById("forwardModal");
  const closeModalBtns = document.querySelectorAll(".close-modal");

  document.querySelectorAll(".view-details-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      // Update State
      currentReportId = this.dataset.id;
      currentReportPhone = this.dataset.phone || "";
      currentReportStatus = this.dataset.status;

      // Fill UI
      document.getElementById("modalTitle").textContent =
        "Report #" + currentReportId;
      document.getElementById("modalCategory").textContent =
        this.dataset.category || "—";
      document.getElementById("modalDescription").textContent =
        this.dataset.description || "—";
      document.getElementById("modalReporter").textContent =
        this.dataset.reporter || "—";
      document.getElementById("modalPhone").textContent =
        currentReportPhone || "N/A";
      document.getElementById("modalStatus").textContent =
        currentReportStatus || "—";
      document.getElementById("modalAgency").textContent =
        this.dataset.agency || "—";
      document.getElementById("modalDate").textContent =
        this.dataset.date || "—";

      // Photo Logic
      const photoWrapper = document.getElementById("modalPhotoWrapper");
      const photoEl = document.getElementById("modalPhoto");
      if (this.dataset.photo && this.dataset.photo !== "") {
        photoEl.src = this.dataset.photo;
        photoWrapper.style.display = "block";
      } else {
        photoWrapper.style.display = "none";
        photoEl.src = "";
      }

      reportModal.style.display = "flex";
      document.body.style.overflow = "hidden";

      // Close any open meatball menus
      document
        .querySelectorAll(".meatballs-menu.open")
        .forEach((m) => m.classList.remove("open"));
    });
  });

  // Close buttons/Window clicks
  function closeAllModals() {
    if (reportModal) reportModal.style.display = "none";
    if (forwardModal) forwardModal.style.display = "none";
    document.body.style.overflow = "";
  }

  closeAllModals; // Assign function to escape or close logic
  closeModalBtns.forEach((btn) =>
    btn.addEventListener("click", closeAllModals),
  );

  window.addEventListener("click", (e) => {
    if (e.target === reportModal || e.target === forwardModal) closeAllModals();
  });

  const cancelForward = document.getElementById("cancelForwardModal");
  if (cancelForward) cancelForward.addEventListener("click", closeAllModals);

  // ── FORWARD TO AGENCY ───────────────────────────────────────
  window.showForwardModal = function (reportId) {
    document.getElementById("forwardReportId").value = reportId;
    forwardModal.style.display = "flex";
    document.body.style.overflow = "hidden";
    document
      .querySelectorAll(".meatballs-menu.open")
      .forEach((m) => m.classList.remove("open"));
  };

  // ── SMS NOTIFICATIONS ───────────────────────────────────────
  const STATUS_MESSAGES = {
    Pending: "AGAP-Link: Your report (#…) has been received.",
    Verified: "AGAP-Link: Your report (#…) has been verified.",
    Forwarded: "AGAP-Link: Your report (#…) was forwarded to authorities.",
    Ongoing: "AGAP-Link: Your report (#…) is being handled.",
    Resolved: "AGAP-Link: Your report (#…) has been resolved. Thank you!",
  };

  const smsBtn = document.getElementById("messageCitizenBtn");
  if (smsBtn) {
    smsBtn.addEventListener("click", function () {
      if (!currentReportId || !currentReportStatus) {
        alert("❌ No report selected.");
        return;
      }
      if (!currentReportPhone) {
        alert("❌ No phone number on record for this reporter.");
        return;
      }

      const previewMessage = STATUS_MESSAGES[currentReportStatus]
        ? STATUS_MESSAGES[currentReportStatus].replace(/…/g, currentReportId)
        : `AGAP-Link: Your report (#${currentReportId}) status updated to ${currentReportStatus}.`;

      if (
        !confirm(
          `Send SMS to ${currentReportPhone}?\n\nMessage: ${previewMessage}`,
        )
      )
        return;

      smsBtn.textContent = "Sending…";
      smsBtn.disabled = true;

      fetch(baseUrl + "/controller/send_sms.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          report_id: currentReportId,
          status: currentReportStatus,
        }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) alert("✅ SMS sent successfully!");
          else alert("❌ Failed: " + (data.error || "Unknown error."));
        })
        .catch((err) => alert("❌ Network error: " + err.message))
        .finally(() => {
          smsBtn.textContent = "Message Citizen via SMS";
          smsBtn.disabled = false;
        });
    });
  }

  // ── AUTO-DISMISS ALERTS ─────────────────────────────────────
  document.querySelectorAll(".alert").forEach((alert) => {
    setTimeout(() => {
      alert.style.transition = "opacity 0.5s ease";
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // ── EXCEL EXPORT (SheetJS) ──────────────────────────────────
  const exportBtn = document.getElementById("exportReportsBtn");
  if (exportBtn) exportBtn.addEventListener("click", exportReportsToExcel);

  function exportReportsToExcel() {
    showExportToast("Preparing export…");
    setTimeout(() => {
      try {
        const rows = document.querySelectorAll("#reportsTable tbody tr");
        if (!rows.length) {
          showExportToast("No reports to export.", true);
          return;
        }

        const headers = [
          "Report ID",
          "Category",
          "Description",
          "Reporter",
          "Status",
          "Verified",
          "Forwarded To",
          "Date Submitted",
        ];
        const data = [headers];

        rows.forEach((row) => {
          data.push([
            row.dataset.reportId || "",
            row.dataset.category || "",
            row.dataset.description || "",
            row.dataset.reporter || "",
            row.dataset.status || "",
            row.dataset.verified || "",
            row.dataset.agency || "",
            row.dataset.date || "",
          ]);
        });

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Reports");

        const filename = `agaplink_reports_${new Date().toISOString().slice(0, 10)}.xlsx`;
        XLSX.writeFile(wb, filename);

        showExportToast(`✓ Exported successfully!`, false, true); // SUCCESS MESSAGE OF EXPORTING TO EXCEL
      } catch (err) {
        console.error(err);
        showExportToast("Export failed.", true);
      }
    }, 50);
  }

  function showExportToast(message, isError = false, isSuccess = false) {
    const toast = document.getElementById("exportToast");
    const msgEl = document.getElementById("exportToastMsg");
    if (!toast || !msgEl) return;

    msgEl.textContent = message;
    toast.style.display = "flex";
    toast.style.opacity = "1";
    if (isError) toast.classList.add("export-toast--error");
    if (isSuccess) toast.classList.add("export-toast--success");

    setTimeout(() => {
      toast.style.opacity = "0";
      setTimeout(() => (toast.style.display = "none"), 500);
    }, 3000);
  }
});

// document.addEventListener("DOMContentLoaded", () => {
//   // ── MEATBALLS MENU ────────────────────────────────────────────────────
//   document.querySelectorAll(".meatballs-btn").forEach((btn) => {
//     btn.addEventListener("click", (e) => {
//       e.stopPropagation();
//       document
//         .querySelectorAll(".meatballs-menu")
//         .forEach((m) => (m.style.display = "none"));
//       btn.nextElementSibling.style.display = "block";
//     });
//   });

//   document.addEventListener("click", () => {
//     document
//       .querySelectorAll(".meatballs-menu")
//       .forEach((m) => (m.style.display = "none"));
//   });

//   // ── MODAL ─────────────────────────────────────────────────────────────
//   const modal = document.getElementById("reportModal");
//   const closeModal = document.querySelector(".close-modal");
//   const messageCitizenBtn = document.getElementById("messageCitizenBtn");

//   let currentReportId = null;
//   let currentReportPhone = null;
//   let currentReportStatus = null; // ← FIX: was never declared — caused silent ReferenceError on every button click

//   document.querySelectorAll(".view-details-btn").forEach((btn) => {
//     btn.addEventListener("click", () => {
//       currentReportId = btn.dataset.id;
//       currentReportPhone = btn.dataset.phone || "";
//       currentReportStatus = btn.dataset.status; // ← FIX: was never assigned — stayed null/undefined forever

//       document.getElementById("modalTitle").textContent =
//         "Report #" + currentReportId;
//       document.getElementById("modalCategory").textContent =
//         btn.dataset.category;
//       document.getElementById("modalDescription").textContent =
//         btn.dataset.description;
//       document.getElementById("modalReporter").textContent =
//         btn.dataset.reporter;
//       document.getElementById("modalPhone").textContent =
//         currentReportPhone || "N/A";
//       document.getElementById("modalStatus").textContent = btn.dataset.status;
//       document.getElementById("modalAgency").textContent =
//         btn.dataset.agency || "—";
//       document.getElementById("modalDate").textContent = btn.dataset.date;

//       // PHOTO
//       const photoWrapper = document.getElementById("modalPhotoWrapper");
//       const photoEl = document.getElementById("modalPhoto");

//       if (btn.dataset.photo && btn.dataset.photo !== "") {
//         photoEl.src = btn.dataset.photo;
//         photoWrapper.style.display = "block";
//       } else {
//         photoWrapper.style.display = "none";
//         photoEl.src = "";
//       }

//       modal.style.display = "flex";
//     });
//   });

//   closeModal.addEventListener("click", () => {
//     modal.style.display = "none";
//   });

//   modal.addEventListener("click", (e) => {
//     if (e.target === modal) modal.style.display = "none";
//   });

//   // ── MESSAGE CITIZEN (PhilSMS — AUTO-GENERATED MESSAGE) ────────────────
//   const STATUS_MESSAGES = {
//     Pending: "AGAP-Link: Your report (#…) has been received.",
//     Verified: "AGAP-Link: Your report (#…) has been verified.",
//     Forwarded: "AGAP-Link: Your report (#…) was forwarded to authorities.",
//     Ongoing: "AGAP-Link: Your report (#…) is being handled.",
//     Resolved: "AGAP-Link: Your report (#…) has been resolved. Thank you!",
//   };

//   messageCitizenBtn.addEventListener("click", () => {
//     if (!currentReportId || !currentReportStatus) {
//       alert("❌ No report selected.");
//       return;
//     }

//     const previewMessage = STATUS_MESSAGES[currentReportStatus]
//       ? STATUS_MESSAGES[currentReportStatus].replace(/…/g, currentReportId)
//       : "AGAP-Link: Your report (#" +
//         currentReportId +
//         ") status updated to " +
//         currentReportStatus +
//         ".";

//     const confirmed = confirm(
//       "📩 Send SMS Notification?\n\n" +
//         "To      : " +
//         (currentReportPhone || "citizen (no phone on record)") +
//         "\n" +
//         "Status  : " +
//         currentReportStatus +
//         "\n" +
//         "Message : " +
//         previewMessage +
//         "\n\n" +
//         "Click OK to send.",
//     );

//     if (!confirmed) return;

//     const baseUrl = document.body.dataset.baseUrl || "";

//     messageCitizenBtn.textContent = "Sending…";
//     messageCitizenBtn.disabled = true;

//     fetch(baseUrl + "/controller/send_sms.php", {
//       method: "POST",
//       headers: { "Content-Type": "application/json" },
//       body: JSON.stringify({
//         report_id: currentReportId,
//         status: currentReportStatus,
//       }),
//     })
//       .then((r) => {
//         const ct = r.headers.get("content-type") || "";
//         if (!ct.includes("application/json")) {
//           throw new Error(
//             "Server returned a non-JSON response (HTTP " +
//               r.status +
//               "). Check PHP error log.",
//           );
//         }
//         return r.json();
//       })
//       .then((data) => {
//         if (data.success) {
//           alert(
//             "✅ SMS sent successfully!\n\n" +
//               "To      : " +
//               (currentReportPhone || "citizen") +
//               "\n" +
//               "Message : " +
//               previewMessage,
//           );
//         } else {
//           alert("❌ Failed to send SMS: " + (data.error || "Unknown error."));
//         }
//       })
//       .catch((err) => {
//         console.error("[SMS]", err);
//         alert("❌ Network or server error:\n" + err.message);
//       })
//       .finally(() => {
//         messageCitizenBtn.textContent = "Message Citizen via SMS";
//         messageCitizenBtn.disabled = false;
//       });
//   });

//   // ── FORWARD MODAL ─────────────────────────────────────────────────────
//   const forwardModal = document.getElementById("forwardModal");
//   const forwardReportIdInput = document.getElementById("forwardReportId");

//   window.showForwardModal = function (reportId) {
//     forwardReportIdInput.value = reportId;
//     forwardModal.style.display = "flex";
//   };

//   const closeForwardModal = document.getElementById("closeForwardModal");
//   const cancelForwardModal = document.getElementById("cancelForwardModal");

//   if (closeForwardModal) {
//     closeForwardModal.addEventListener("click", () => {
//       forwardModal.style.display = "none";
//     });
//   }
//   if (cancelForwardModal) {
//     cancelForwardModal.addEventListener("click", () => {
//       forwardModal.style.display = "none";
//     });
//   }
//   if (forwardModal) {
//     forwardModal.addEventListener("click", (e) => {
//       if (e.target === forwardModal) forwardModal.style.display = "none";
//     });
//   }
// });
