document.addEventListener("DOMContentLoaded", () => {

    // ── MEATBALLS MENU ────────────────────────────────────────────────────
    document.querySelectorAll(".meatballs-btn").forEach(btn => {
        btn.addEventListener("click", e => {
            e.stopPropagation();
            document.querySelectorAll(".meatballs-menu").forEach(m => m.style.display = "none");
            btn.nextElementSibling.style.display = "block";
        });
    });
    document.addEventListener("click", () => {
        document.querySelectorAll(".meatballs-menu").forEach(m => m.style.display = "none");
    });

    // ── MODAL ─────────────────────────────────────────────────────────────
    const modal             = document.getElementById("reportModal");
    const closeModal        = document.querySelector(".close-modal");
    const messageCitizenBtn = document.getElementById("messageCitizenBtn");

    let currentReportId    = null;
    let currentReportPhone = null;

    document.querySelectorAll(".view-details-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            currentReportId    = btn.dataset.id;
            currentReportPhone = btn.dataset.phone || '';

            document.getElementById("modalTitle").textContent       = "Report #" + currentReportId;
            document.getElementById("modalCategory").textContent    = btn.dataset.category;
            document.getElementById("modalDescription").textContent = btn.dataset.description;
            document.getElementById("modalReporter").textContent    = btn.dataset.reporter;
            document.getElementById("modalPhone").textContent       = currentReportPhone || "N/A";
            document.getElementById("modalStatus").textContent      = btn.dataset.status;
            document.getElementById("modalAgency").textContent      = btn.dataset.agency || "—";
            document.getElementById("modalDate").textContent        = btn.dataset.date;

            // Photo
            const photoWrapper = document.getElementById("modalPhotoWrapper");
            const photoEl      = document.getElementById("modalPhoto");
            if (btn.dataset.photo && btn.dataset.photo !== '') {
                const baseUrl = document.body.dataset.baseUrl || '';
                photoEl.src                = baseUrl + btn.dataset.photo;
                photoWrapper.style.display = "block";
            } else {
                photoWrapper.style.display = "none";
                photoEl.src = '';
            }

            modal.style.display = "flex";
        });
    });

    closeModal.addEventListener("click", () => { modal.style.display = "none"; });
    modal.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });

    // ── MESSAGE CITIZEN (Semaphore SMS) ───────────────────────────────────
    messageCitizenBtn.addEventListener("click", () => {
        if (!currentReportId) return;

        const promptMsg = prompt(
            "Enter a custom message to send to the citizen via SMS.\n\n" +
            "Leave blank to send the default status update message."
        );

        if (promptMsg === null) return; // user cancelled

        const formData = new FormData();
        formData.append("report_id", currentReportId);
        formData.append("custom_message", promptMsg.trim());

        const baseUrl = document.body.dataset.baseUrl || '';

        messageCitizenBtn.textContent = "Sending…";
        messageCitizenBtn.disabled    = true;

        fetch(baseUrl + "/controller/send_sms.php", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert("✅ SMS sent successfully to " + (currentReportPhone || "the citizen") + ".");
            } else {
                alert("❌ Failed to send SMS: " + (data.message || "Unknown error."));
            }
        })
        .catch(err => {
            alert("❌ Network error: " + err.message);
        })
        .finally(() => {
            messageCitizenBtn.textContent = "Message Citizen via SMS";
            messageCitizenBtn.disabled    = false;
        });
    });

    // ── FORWARD MODAL ────────────────────────────────────────────────────
    const forwardModal = document.getElementById("forwardModal");
    const forwardReportIdInput = document.getElementById("forwardReportId");
    
    // Make showForwardModal globally available
    window.showForwardModal = function(reportId) {
        forwardReportIdInput.value = reportId;
        forwardModal.style.display = "flex";
    };

    const closeForwardModal = document.getElementById("closeForwardModal");
    const cancelForwardModal = document.getElementById("cancelForwardModal");

    if (closeForwardModal) {
        closeForwardModal.addEventListener("click", () => { forwardModal.style.display = "none"; });
    }
    if (cancelForwardModal) {
        cancelForwardModal.addEventListener("click", () => { forwardModal.style.display = "none"; });
    }
    if (forwardModal) {
        forwardModal.addEventListener("click", e => { if (e.target === forwardModal) forwardModal.style.display = "none"; });
    }
});
