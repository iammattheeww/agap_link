document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // 1. MOBILE TOP NAV + SLIDE-IN SIDEBAR
  // ============================================
  const hamburger = document.querySelector(".topnav-hamburger");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".mobile-overlay");

  if (hamburger && sidebar) {
    hamburger.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("sidebar-open");
      overlay && overlay.classList.toggle("active");
      this.textContent = sidebar.classList.contains("sidebar-open")
        ? "✕"
        : "☰";
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      if (sidebar) sidebar.classList.remove("sidebar-open");
      overlay.classList.remove("active");
      if (hamburger) hamburger.textContent = "☰";
    });
  }

  // ============================================
  // 2. REPORT AN ISSUE MODAL TOGGLES
  // ============================================
  const openReportModalBtns = document.querySelectorAll(
    "#openReportModal, .btn-report-issue",
  );
  const closeReportModalBtn = document.getElementById("closeReportModal");
  const reportModal = document.getElementById("reportModal");

  openReportModalBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      if (reportModal) reportModal.classList.add("active");

      // INIT MAP AFTER MODAL IS VISIBLE
      setTimeout(() => {
        if (typeof initReportMap === "function") {
          initReportMap();
        }
      }, 150);
    });
  });

  if (closeReportModalBtn && reportModal) {
    closeReportModalBtn.addEventListener("click", function () {
      reportModal.classList.remove("active");
    });
  }

  // ============================================
  // 3. FILTER DROPDOWN & LIVE CARD FILTERING
  // ============================================
  const filterToggle = document.getElementById("filterToggle");
  const filterMenu = document.querySelector(".filter-menu");
  const filterButtons = document.querySelectorAll(".filter-btn");
  const selectedFilterText = document.getElementById("selectedFilter");
  const reportCards = document.querySelectorAll(".report-card");

  if (filterToggle && filterMenu) {
    filterToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      filterMenu.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      if (
        filterMenu &&
        !filterMenu.contains(e.target) &&
        filterToggle &&
        !filterToggle.contains(e.target)
      ) {
        filterMenu.classList.remove("show");
      }
    });
  }

  if (filterButtons.length > 0) {
    filterButtons.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        filterButtons.forEach((b) => b.classList.remove("active"));
        this.classList.add("active");

        const filterValue = this.getAttribute("data-filter");
        if (selectedFilterText) {
          selectedFilterText.textContent =
            filterValue.charAt(0).toUpperCase() + filterValue.slice(1);
        }

        reportCards.forEach((card) => {
          if (
            filterValue === "all" ||
            card.getAttribute("data-status") === filterValue
          ) {
            card.style.display = "";
            card.classList.remove("hidden");
          } else {
            card.style.display = "none";
            card.classList.add("hidden");
          }
        });
        if (filterMenu) filterMenu.classList.remove("show");
      });
    });
  }

  // ============================================
  // 4. LIVE SEARCH REPORTS FUNCTION
  // ============================================
  const reportSearch = document.getElementById("reportSearch");
  if (reportSearch) {
    reportSearch.addEventListener("input", function () {
      const query = this.value.toLowerCase().trim();
      reportCards.forEach((card) => {
        const description = (
          card.getAttribute("data-description") || ""
        ).toLowerCase();
        const address = (card.getAttribute("data-address") || "").toLowerCase();
        const category = (
          card.getAttribute("data-category") || ""
        ).toLowerCase();

        if (
          description.includes(query) ||
          address.includes(query) ||
          category.includes(query)
        ) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });
    });
  }

  // ============================================
  // 5. REPORT DETAIL VIEW MODAL & TIMELINE
  // ============================================
  const reportDetailModal = document.getElementById("reportDetailModal");
  const closeModalBtn = document.getElementById("closeModal");

  if (reportCards.length > 0 && reportDetailModal) {
    reportCards.forEach((card) => {
      card.addEventListener("click", function (e) {
        if (
          e.target.closest(".delete-report-form") ||
          e.target.closest(".btn-delete-report")
        ) {
          return;
        }

        const category = this.getAttribute("data-category") || "Report";
        const status = this.getAttribute("data-status") || "pending";
        const statusText = this.getAttribute("data-status-text") || "PENDING";
        const address = this.getAttribute("data-address") || "";
        const date = this.getAttribute("data-date") || "";
        const description = this.getAttribute("data-description") || "";

        // Populate Modal Fields
        document.getElementById("modalCategory").textContent = category;
        document.getElementById("modalStatus").textContent = statusText;
        document.getElementById("modalAddress").textContent = address;
        document.getElementById("modalDate").textContent = date;
        document.getElementById("modalDescription").textContent = description;

        // Timeline Logic
        document
          .querySelectorAll(".timeline-item")
          .forEach((item) => item.classList.remove("active"));

        let progressHeight = 0;
        const step1 = document.querySelector('[data-step="1"]');
        const step2 = document.querySelector('[data-step="2"]');
        const step3 = document.querySelector('[data-step="3"]');
        const tSub = document.getElementById("timelineSubmitted");
        const tOng = document.getElementById("timelineOngoing");
        const tRes = document.getElementById("timelineResolved");
        const tProg = document.getElementById("timelineProgress");

        if (step1) step1.classList.add("active");
        if (tSub) tSub.textContent = date;
        progressHeight = 33;

        if (
          status === "ongoing" ||
          status === "forwarded" ||
          status === "verified"
        ) {
          if (step2) step2.classList.add("active");
          if (tOng) tOng.textContent = date;
          progressHeight = 66;
        }

        if (status === "resolved") {
          if (step2) step2.classList.add("active");
          if (step3) step3.classList.add("active");
          if (tOng) tOng.textContent = date;
          if (tRes) tRes.textContent = date;
          progressHeight = 100;
        }

        if (tProg) tProg.style.height = progressHeight + "%";

        reportDetailModal.classList.add("active");

        setTimeout(() => {
          if (typeof initReportMap === "function") {
            initReportMap();
          }
        }, 200);
      });
    });
  }

  if (closeModalBtn && reportDetailModal) {
    closeModalBtn.addEventListener("click", function () {
      reportDetailModal.classList.remove("active");
    });
  }

  if (reportDetailModal) {
    reportDetailModal.addEventListener("click", function (e) {
      if (e.target === reportDetailModal) {
        this.classList.remove("active");
      }
    });
  }

  // ============================================
  // 6. FILE UPLOAD HANDLER
  // ============================================
  const photoInput = document.getElementById("photo");
  const fileUploadArea = document.getElementById("fileUploadArea");
  const uploadPlaceholder = document.getElementById("uploadPlaceholder");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeImageBtn = document.getElementById("removeImageBtn");

  if (photoInput) {
    // File input change event
    photoInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        handleFileSelect(file);
      }
    });

    // Drag and drop
    if (fileUploadArea) {
      fileUploadArea.addEventListener("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add("drag-over");
      });

      fileUploadArea.addEventListener("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove("drag-over");
      });

      fileUploadArea.addEventListener("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove("drag-over");
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          photoInput.files = files;
          handleFileSelect(files[0]);
        }
      });
    }
  }

  if (removeImageBtn) {
    removeImageBtn.addEventListener("click", function (e) {
      e.preventDefault();
      photoInput.value = "";
      if (uploadPlaceholder) uploadPlaceholder.style.display = "block";
      if (previewContainer) previewContainer.style.display = "none";
      if (previewImage) previewImage.src = "";
      removeImageBtn.style.display = "none";
    });
  }

  function handleFileSelect(file) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ["image/jpeg", "image/png"];

    if (!allowedTypes.includes(file.type)) {
      alert("Only JPG and PNG files are allowed.");
      photoInput.value = "";
      return;
    }

    if (file.size > maxSize) {
      alert("File size must not exceed 5MB.");
      photoInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      if (previewImage) previewImage.src = e.target.result;
      if (uploadPlaceholder) uploadPlaceholder.style.display = "none";
      if (previewContainer) previewContainer.style.display = "block";
      if (removeImageBtn) removeImageBtn.style.display = "block";
    };
    reader.readAsDataURL(file);
  }

  // ============================================
  // 7. DELETE ACCOUNT MODAL AND CONFIRMATION
  // ============================================
  const deleteInput = document.getElementById("delete_confirmation");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

  if (deleteInput && confirmDeleteBtn) {
    deleteInput.addEventListener("input", function () {
      confirmDeleteBtn.disabled = this.value.trim() !== "DELETE";
    });
  }

  window.openDeleteModal = function () {
    const dModal = document.getElementById("deleteModal");
    if (dModal) dModal.classList.add("active");
  };

  window.closeDeleteModal = function () {
    const dModal = document.getElementById("deleteModal");
    if (dModal) {
      dModal.classList.remove("active");
      if (deleteInput) deleteInput.value = "";
      if (confirmDeleteBtn) confirmDeleteBtn.disabled = true;
    }
  };
});

// ============================================
// 7. REPORT MAP INITIALIZATION (Global)
// ============================================
let reportMapInstance = null;

function initReportMap() {
  const mapEl = document.getElementById("reportMap");
  if (!mapEl || typeof L === "undefined") return;

  if (reportMapInstance) {
    setTimeout(() => reportMapInstance.invalidateSize(), 200);
    return;
  }

  const latInput = document.getElementById("gps_lat");
  const lngInput = document.getElementById("gps_long");
  const addressInput = document.getElementById("address");

  const defaultLat = 11.5833;
  const defaultLng = 122.75;

  const map = L.map(mapEl).setView([defaultLat, defaultLng], 13);
  reportMapInstance = map;

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap",
  }).addTo(map);

  const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(
    map,
  );

  function updateLocation(lat, lng) {
    if (latInput) latInput.value = lat;
    if (lngInput) lngInput.value = lng;

    fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
    )
      .then((r) => r.json())
      .then((d) => {
        if (addressInput && d.display_name) addressInput.value = d.display_name;
      });
  }

  marker.on("dragend", () => {
    const p = marker.getLatLng();
    updateLocation(p.lat, p.lng);
  });

  map.on("click", (e) => {
    marker.setLatLng(e.latlng);
    updateLocation(e.latlng.lat, e.latlng.lng);
  });

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      map.setView([lat, lng], 17);
      marker.setLatLng([lat, lng]);
      updateLocation(lat, lng);
    });
  }

  setTimeout(() => map.invalidateSize(), 200);
}
