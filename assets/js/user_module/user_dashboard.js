document.addEventListener("DOMContentLoaded", function () {
  // THIS VARIABLE IS FOR THE CONDITIONAL STATEMENTS THAT OPENS THE MODAL
  const modal = document.getElementById("reportModal");
  const openBtn = document.getElementById("openReportModal");

  // THIS VARIABLE IS FOR THE CONDITIONAL STATEMENTS THAT CLOSES THE MODAL
  const closeBtn = document.getElementById("closeReportModal");
  const cancelBtn = document.getElementById("cancelReportBtn");

  // CLICK EVENT LISTENER FOR THE BUTTON
  function openModal() {
    if (modal) modal.classList.add("active");
  }
  function closeModal() {
    if (modal) modal.classList.remove("active"); 
  }

  // OPENS THE MODAL
  if (openBtn) openBtn.addEventListener("click", openModal); // CALLS THE openModal function -> openBtn is openModal()
  if (closeBtn) closeBtn.addEventListener("click", closeModal); // CALLS THE closeModal function -> closeBtn is closeModal()
  if (cancelBtn) cancelBtn.addEventListener("click", closeModal); // CALLS THE closeModal function -> cancelBtn is closeModal()
  
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeModal();
    });
  }

  // GEOLOCATION API
  const getLocationBtn = document.getElementById("getLocationBtn");
  const locationStatus = document.getElementById("locationStatus");
  const latInput = document.getElementById("gps_lat");
  const longInput = document.getElementById("gps_long");

  if (getLocationBtn) {
    getLocationBtn.addEventListener("click", function () {
      if (!navigator.geolocation) {
        if (locationStatus)
          locationStatus.innerHTML = "Geolocation is not supported.";
        return;
      }
      if (locationStatus) locationStatus.innerHTML = "Getting location...";
      navigator.geolocation.getCurrentPosition(
        function (position) {
          if (latInput) latInput.value = position.coords.latitude;
          if (longInput) longInput.value = position.coords.longitude;
          if (locationStatus)
            locationStatus.innerHTML = "✅ Location captured successfully.";
        },
        function () {
          if (locationStatus)
            locationStatus.innerHTML = "❌ Unable to retrieve location.";
        },
      );
    });
  }

  // FILE UPLOAD PREVIEW
  const fileUploadArea = document.getElementById("fileUploadArea");
  const fileInput = document.getElementById("photo");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeBtn = document.getElementById("removeImageBtn");

  if (fileUploadArea && fileInput) {
    fileUploadArea.addEventListener("click", function (e) {
      // FIXED DOUBLE FILE DIALOG BY PREVENTING CLICK BUBBLING FROM INPUT/PREVIEW/REMOVE ELEMENTS AND ADDING GUARDS, WITH STRUCTURAL FIX OF MOVING INPUT OUTSIDE THE CONTAINER AS PRIMARY SOLUTION.
      if (
        e.target === fileInput ||
        e.target === previewImage ||
        e.target === removeBtn
      )
        return;

      fileInput.click();
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        if (previewImage) previewImage.src = e.target.result;
        if (previewContainer) previewContainer.style.display = "block";
      };
      reader.readAsDataURL(file);
    });
  }

  if (removeBtn) {
    removeBtn.addEventListener("click", function (e) {
      // FIXED STOPPROPAGATION TO PREVENT REMOVE CLICK FROM BUBBLING AND REOPENING THE FILE DIALOG.
      e.stopPropagation();
      if (fileInput) fileInput.value = "";
      if (previewContainer) previewContainer.style.display = "none";
      if (previewImage) previewImage.src = "";
    });
  }

  // INITIALIZE LEAFLET MAP WHEN MODAL OPENS
  if (openBtn) {
    openBtn.addEventListener("click", function () {
      setTimeout(() => {
        if (typeof initReportMap === "function") initReportMap();
      }, 150);
    });
  }
});
