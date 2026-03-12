document.addEventListener("DOMContentLoaded", () => {
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

            document.querySelectorAll(".view-details-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    document.getElementById("modalTitle").textContent       = "Report #" + btn.dataset.id;
                    document.getElementById("modalCategory").textContent    = btn.dataset.category;
                    document.getElementById("modalDescription").textContent = btn.dataset.description;
                    document.getElementById("modalReporter").textContent    = btn.dataset.reporter;
                    document.getElementById("modalPhone").textContent       = btn.dataset.phone || "N/A";
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
        });
});
