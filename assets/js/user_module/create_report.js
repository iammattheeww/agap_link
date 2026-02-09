// FIXED VERSION - Wrapped in DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  // FILE UPLOAD HANDLING
  const fileUploadArea = document.getElementById("fileUploadArea");
  const fileInput = document.getElementById("photo");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeImageBtn = document.getElementById("removeImageBtn");

  // Click to upload
  fileUploadArea.addEventListener("click", () => fileInput.click());

  // File selection
  fileInput.addEventListener("change", handleFileSelect);

  // Drag and drop
  fileUploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    fileUploadArea.classList.add("drag-over");
  });

  fileUploadArea.addEventListener("dragleave", () => {
    fileUploadArea.classList.remove("drag-over");
  });

  fileUploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    fileUploadArea.classList.remove("drag-over");
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      fileInput.files = files;
      handleFileSelect();
    }
  });

  function handleFileSelect() {
    const file = fileInput.files[0];
    if (file) {
      // Validate file size (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        alert("File size must be less than 5MB");
        fileInput.value = "";
        return;
      }

      // Validate file type
      if (!["image/jpeg", "image/jpg", "image/png"].includes(file.type)) {
        alert("Only JPG, JPEG, and PNG files are allowed");
        fileInput.value = "";
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImage.src = e.target.result;
        previewContainer.style.display = "block";
        fileUploadArea.style.display = "none";
      };
      reader.readAsDataURL(file);
    }
  }

  // Remove image
  removeImageBtn.addEventListener("click", () => {
    fileInput.value = "";
    previewContainer.style.display = "none";
    fileUploadArea.style.display = "block";
    previewImage.src = "";
  });

  // GPS LOCATION HANDLING
  const getLocationBtn = document.getElementById("getLocationBtn");
  const locationStatus = document.getElementById("locationStatus");
  const gpsLatInput = document.getElementById("gps_lat");
  const gpsLongInput = document.getElementById("gps_long");

  getLocationBtn.addEventListener("click", () => {
    if (!navigator.geolocation) {
      showLocationStatus(
        "Geolocation is not supported by your browser",
        "error",
      );
      return;
    }

    getLocationBtn.disabled = true;
    getLocationBtn.textContent = "📍 Getting location...";

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const long = position.coords.longitude;

        gpsLatInput.value = lat;
        gpsLongInput.value = long;

        showLocationStatus(
          `Location captured: ${lat.toFixed(6)}, ${long.toFixed(6)}`,
          "success",
        );

        getLocationBtn.textContent = "✓ Location Captured";
        setTimeout(() => {
          getLocationBtn.textContent = "📍 Get My Current Location";
          getLocationBtn.disabled = false;
        }, 3000);
      },
      (error) => {
        let errorMessage = "Unable to get location. ";
        switch (error.code) {
          case error.PERMISSION_DENIED:
            errorMessage += "Please allow location access.";
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage += "Location information unavailable.";
            break;
          case error.TIMEOUT:
            errorMessage += "Request timed out.";
            break;
        }
        showLocationStatus(errorMessage, "error");
        getLocationBtn.textContent = "📍 Get My Current Location";
        getLocationBtn.disabled = false;
      },
    );
  });

  function showLocationStatus(message, type) {
    locationStatus.textContent = message;
    locationStatus.className = "location-status location-" + type;
  }

  // FORM VALIDATION
  document
    .getElementById("createReportForm")
    .addEventListener("submit", (e) => {
      const description = document.getElementById("description").value.trim();
      const address = document.getElementById("address").value.trim();
      const category = document.getElementById("category_id").value;

      if (!category || !description || !address) {
        e.preventDefault();
        alert("Please fill in all required fields");
        return false;
      }
    });
}); // END DOMContentLoaded
