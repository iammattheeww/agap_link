document.addEventListener("DOMContentLoaded", function () {
  // --- ELEMENTS ---
  const filterDropdown = document.querySelector(".filter-dropdown");
  const filterToggle = document.querySelector(".filter-toggle");
  const filterButtons = document.querySelectorAll(".filter-btn");
  const selectedFilter = document.getElementById("selectedFilter");
  const reportCards = document.querySelectorAll(".report-card");

  // --- INITIALIZE ---
  selectedFilter.textContent = "All"; // default filter

  // --- TOGGLE DROPDOWN ---
  filterToggle.addEventListener("click", () => {
    filterDropdown.classList.toggle("show");
  });

  document.addEventListener("click", (e) => {
    if (!filterDropdown.contains(e.target)) {
      filterDropdown.classList.remove("show");
    }
  });

  // --- FILTER REPORT CARDS ---
  filterButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const filter = btn.dataset.filter;

      // Update selected filter text
      selectedFilter.textContent = filter.charAt(0).toUpperCase() + filter.slice(1);

      // Remove active class from all buttons, add to clicked
      filterButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      // Show/hide cards
      reportCards.forEach((card) => {
  if (filter === "all" || card.dataset.status === filter) {
    card.classList.remove("hidden");
  } else {
    card.classList.add("hidden");
  }
});


      // Close dropdown after selection
      filterDropdown.classList.remove("show");
    });
  });

  // --- MODAL HANDLER (Optional, if you still need it) ---
  document.querySelectorAll(".report-card").forEach((card) => {
    card.addEventListener("click", function () {
      const modal = document.getElementById("reportModal");
      document.getElementById("modalCategory").textContent = this.dataset.category;
      document.getElementById("modalStatus").textContent = this.dataset.statusText;
      document.getElementById("modalAddress").textContent = this.dataset.address;
      document.getElementById("modalDate").textContent = this.dataset.date;
      modal.style.display = "flex";
    });
  });

  document.getElementById("closeModal").addEventListener("click", () => {
    document.getElementById("reportModal").style.display = "none";
  });

  document.getElementById("reportModal").addEventListener("click", (e) => {
    if (e.target.id === "reportModal") {
      e.currentTarget.style.display = "none";
    }
  });
});
