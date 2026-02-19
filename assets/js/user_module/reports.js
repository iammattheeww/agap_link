document.addEventListener("DOMContentLoaded", function () {
  // --- FILTER DROPDOWN ---
  const filterDropdown = document.querySelector(".filter-dropdown");
  const filterToggle   = document.querySelector(".filter-toggle");
  const filterButtons  = document.querySelectorAll(".filter-btn");
  const selectedFilter = document.getElementById("selectedFilter");
  const reportCards    = document.querySelectorAll(".report-card");

  if (selectedFilter) selectedFilter.textContent = "All";

  if (filterToggle) {
    filterToggle.addEventListener("click", function () {
      filterDropdown && filterDropdown.classList.toggle("show");
    });
  }

  document.addEventListener("click", function (e) {
    if (filterDropdown && !filterDropdown.contains(e.target)) {
      filterDropdown.classList.remove("show");
    }
  });

  filterButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const filter = btn.dataset.filter;
      if (selectedFilter) selectedFilter.textContent = filter.charAt(0).toUpperCase() + filter.slice(1);
      filterButtons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      reportCards.forEach(function (card) {
        if (filter === "all" || card.dataset.status === filter) {
          card.classList.remove("hidden");
          card.style.display = "";
        } else {
          card.classList.add("hidden");
          card.style.display = "none";
        }
      });
      if (filterDropdown) filterDropdown.classList.remove("show");
    });
  });

  // --- REPORT CARD CLICK → MODAL WITH TIMELINE ---
  const modal = document.getElementById("reportModal");

  reportCards.forEach(function (card) {
    card.addEventListener("click", function () {
      if (!modal) return;

      document.getElementById("modalCategory").textContent   = this.dataset.category  || "";
      document.getElementById("modalStatus").textContent     = this.dataset.statusText || "";
      document.getElementById("modalAddress").textContent    = this.dataset.address    || "";
      document.getElementById("modalDate").textContent       = this.dataset.date       || "";
      document.getElementById("modalDescription").textContent = this.dataset.description || "";

      // Timeline
      const status        = (this.dataset.status || "").toLowerCase();
      const submittedDate = this.dataset.date || "";

      document.querySelectorAll(".timeline-item").forEach(item => item.classList.remove("active"));

      let progressHeight = 0;
      const step1 = document.querySelector('[data-step="1"]');
      const step2 = document.querySelector('[data-step="2"]');
      const step3 = document.querySelector('[data-step="3"]');
      const tSub  = document.getElementById("timelineSubmitted");
      const tOng  = document.getElementById("timelineOngoing");
      const tRes  = document.getElementById("timelineResolved");
      const tProg = document.getElementById("timelineProgress");

      if (step1)  step1.classList.add("active");
      if (tSub)   tSub.textContent = submittedDate;
      progressHeight = 33;

      if (status === "ongoing" || status === "forwarded" || status === "verified") {
        if (step2) step2.classList.add("active");
        if (tOng)  tOng.textContent = "In Progress";
        progressHeight = 66;
      }

      if (status === "resolved") {
        if (step2) step2.classList.add("active");
        if (step3) step3.classList.add("active");
        if (tOng)  tOng.textContent = "Processed";
        if (tRes)  tRes.textContent = "Completed";
        progressHeight = 100;
      }

      if (tProg) tProg.style.height = progressHeight + "%";

      modal.style.display = "flex";
    });
  });

  // --- CLOSE MODAL ---
  const closeModal = document.getElementById("closeModal");
  if (closeModal) {
    closeModal.addEventListener("click", function () {
      if (modal) modal.style.display = "none";
    });
  }

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target.id === "reportModal") this.style.display = "none";
    });
  }

  // --- SEARCH ---
  const searchInput = document.getElementById("reportSearch");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const term = this.value.toLowerCase().trim();
      reportCards.forEach(function (card) {
        const cat  = (card.dataset.category    || "").toLowerCase();
        const addr = (card.dataset.address     || "").toLowerCase();
        const desc = (card.dataset.description || "").toLowerCase();
        card.style.display = (!term || cat.includes(term) || addr.includes(term) || desc.includes(term)) ? "" : "none";
      });
    });
  }
});
