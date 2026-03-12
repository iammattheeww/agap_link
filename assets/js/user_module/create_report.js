document.addEventListener("DOMContentLoaded", function () {

  // FILE UPLOAD HANDLING
  const fileUploadArea = document.getElementById("fileUploadArea");
  const fileInput = document.getElementById("photo");
  const previewContainer = document.getElementById("previewContainer");
  const previewImage = document.getElementById("previewImage");
  const removeImageBtn = document.getElementById("removeImageBtn");
  const uploadPlaceholder = document.getElementById("uploadPlaceholder");

  // Click to upload (guard against clicks on remove button / preview)
  fileUploadArea.addEventListener("click", (e) => {
    if (e.target === removeImageBtn || e.target === previewImage) return;
    fileInput.click();
  });

  // File selection
  fileInput.addEventListener("change", handleFileSelect);

  // Drag and drop
  fileUploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    fileUploadArea.classList.add("dragover");
  });

  fileUploadArea.addEventListener("dragleave", () => {
    fileUploadArea.classList.remove("dragover");
  });

  fileUploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    fileUploadArea.classList.remove("dragover");

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      fileInput.files = files;
      handleFileSelect();
    }
  });

  function handleFileSelect() {
    const file = fileInput.files[0];
    if (!file) return;

    // Validate size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert("File size must be less than 5MB");
      fileInput.value = "";
      return;
    }

    // Validate type
    if (!["image/jpeg", "image/jpg", "image/png"].includes(file.type)) {
      alert("Only JPG, JPEG, and PNG files are allowed");
      fileInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      previewImage.src = e.target.result;

      // SHOW preview, HIDE placeholder
      previewContainer.classList.add("active");
      if (uploadPlaceholder) uploadPlaceholder.style.display = "none";
    };

    reader.readAsDataURL(file);
  }

  // Remove image
  removeImageBtn.addEventListener("click", (e) => {
    e.stopPropagation(); // Prevent bubbling to fileUploadArea
    fileInput.value = "";
    previewImage.src = "";
    previewContainer.classList.remove("active");
    if (uploadPlaceholder) uploadPlaceholder.style.display = "flex";
  });


  /* =========================
     GPS LOCATION HANDLING
  ========================== */

  const getLocationBtn = document.getElementById("getLocationBtn");
  const locationStatus = document.getElementById("locationStatus");
  const gpsLatInput = document.getElementById("gps_lat");
  const gpsLongInput = document.getElementById("gps_long");

  getLocationBtn.addEventListener("click", () => {
    if (!navigator.geolocation) {
      showLocationStatus("Geolocation is not supported by your browser", "error");
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
          "success"
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
      }
    );
  });

  function showLocationStatus(message, type) {
    locationStatus.textContent = message;
    locationStatus.className = "location-status location-" + type;
  }


  /* =========================
     FORM VALIDATION FIX
  ========================== */

  const form = document.querySelector("form");

  form.addEventListener("submit", (e) => {
    const description = document.getElementById("description").value.trim();
    const address = document.getElementById("address").value.trim();
    const category = document.getElementById("category_id").value;

    if (!category || !description || !address) {
      e.preventDefault();
      alert("Please fill in all required fields");
    }
  });

});
