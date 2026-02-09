// FILTER FUNCTIONALITY
document.addEventListener("DOMContentLoaded", function () {
  const filterBtns = document.querySelectorAll(".filter-btn");
  const reportCards = document.querySelectorAll(".report-card");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Remove active class from all buttons
      filterBtns.forEach((b) => b.classList.remove("active"));

      // Add active class to clicked button
      this.classList.add("active");

      // Get filter value
      const filter = this.getAttribute("data-filter");

      // Show/hide cards based on filter
      reportCards.forEach((card) => {
        if (filter === "all" || card.getAttribute("data-status") === filter) {
          card.style.display = "grid";
        } else {
          card.style.display = "none";
        }
      });
    });
  });
});

// EDIT REPORT (Placeholder - implement based on your needs)
function editReport(reportId) {
  alert(
    "Edit functionality for report ID: " + reportId + " - Implement as needed",
  );
  // Redirect to edit page or open modal
  // window.location.href = '/agap_link/view/user_module/edit_report.php?id=' + reportId;
}

// DELETE REPORT
function confirmDelete(reportId) {
  if (
    confirm(
      "Are you sure you want to delete this report? This action cannot be undone.",
    )
  ) {
    // Submit delete request
    window.location.href =
      "/agap_link/controller/report_process.php?action=delete&id=" + reportId;
  }
}
