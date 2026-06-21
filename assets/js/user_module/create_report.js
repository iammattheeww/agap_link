// document.addEventListener("DOMContentLoaded", function () {
//   const fileUploadArea = document.getElementById("fileUploadArea");
//   const fileInput = document.getElementById("photo");
//   const previewContainer = document.getElementById("previewContainer");
//   const previewImage = document.getElementById("previewImage");
//   const removeImageBtn = document.getElementById("removeImageBtn");
//   const uploadPlaceholder = document.getElementById("uploadPlaceholder");

//   if (!fileInput) return;

//   // Native label behavior (label for="photo") handles the click - NO manual trigger needed
//   fileInput.addEventListener("change", handleFileSelect);

//   // DRAG AND DROP
//   if (fileUploadArea) {
//     fileUploadArea.addEventListener("dragover", (e) => {
//       e.preventDefault();
//       fileUploadArea.classList.add("dragover");
//     });

//     fileUploadArea.addEventListener("dragleave", () => {
//       fileUploadArea.classList.remove("dragover");
//     });

//     fileUploadArea.addEventListener("drop", (e) => {
//       e.preventDefault();
//       fileUploadArea.classList.remove("dragover");
//       const files = e.dataTransfer.files;
//       if (files.length > 0) {
//         const dt = new DataTransfer();
//         dt.items.add(files[0]);
//         fileInput.files = dt.files;
//         handleFileSelect();
//       }
//     });
//   }

//   function handleFileSelect() {
//     const file = fileInput.files[0];
//     if (!file) return;

//     // VALIDATE SIZE (5MB)
//     if (file.size > 5 * 1024 * 1024) {
//       alert("File size must be less than 5MB");
//       fileInput.value = "";
//       return;
//     }

//     // VALIDATE TYPE
//     if (!["image/jpeg", "image/jpg", "image/png"].includes(file.type)) {
//       alert("Only JPG, JPEG, and PNG files are allowed");
//       fileInput.value = "";
//       return;
//     }

//     const reader = new FileReader();
//     reader.onload = (e) => {
//       previewImage.src = e.target.result;
//       if (uploadPlaceholder) uploadPlaceholder.style.display = "none";
//       previewContainer.style.display = "flex";
//       previewContainer.classList.add("active");
//       if (removeImageBtn) removeImageBtn.style.display = "inline-block";
//       fileUploadArea.style.pointerEvents = "none";
//       fileUploadArea.style.cursor = "default";
//     };
//     reader.readAsDataURL(file);
//   }

//   // REMOVE IMAGE
//   if (removeImageBtn) {
//     removeImageBtn.addEventListener("click", (e) => {
//       e.preventDefault();
//       e.stopPropagation();
//       fileInput.value = "";
//       previewImage.src = "";
//       previewContainer.style.display = "none";
//       previewContainer.classList.remove("active");
//       if (uploadPlaceholder) uploadPlaceholder.style.display = "flex";
//       removeImageBtn.style.display = "none";
//       fileUploadArea.style.pointerEvents = "auto";
//       fileUploadArea.style.cursor = "pointer";
//     });
//   }

//   // GPS LOCATION HANDLING (keep existing code)
//   const getLocationBtn = document.getElementById("getLocationBtn");
//   const locationStatus = document.getElementById("locationStatus");
//   const gpsLatInput = document.getElementById("gps_lat");
//   const gpsLongInput = document.getElementById("gps_long");

//   if (getLocationBtn) {
//     getLocationBtn.addEventListener("click", () => {
//       if (!navigator.geolocation) {
//         showLocationStatus(
//           "Geolocation is not supported by your browser",
//           "error",
//         );
//         return;
//       }

//       getLocationBtn.disabled = true;
//       getLocationBtn.textContent = "📍 Getting location...";

//       navigator.geolocation.getCurrentPosition(
//         (position) => {
//           const lat = position.coords.latitude;
//           const long = position.coords.longitude;
//           gpsLatInput.value = lat;
//           gpsLongInput.value = long;
//           showLocationStatus(
//             `Location captured: ${lat.toFixed(6)}, ${long.toFixed(6)}`,
//             "success",
//           );
//           getLocationBtn.textContent = "✓ Location Captured";
//           setTimeout(() => {
//             getLocationBtn.textContent = "📍 Get My Current Location";
//             getLocationBtn.disabled = false;
//           }, 3000);
//         },
//         (error) => {
//           let errorMessage = "Unable to get location. ";
//           switch (error.code) {
//             case error.PERMISSION_DENIED:
//               errorMessage += "Please allow location access.";
//               break;
//             case error.POSITION_UNAVAILABLE:
//               errorMessage += "Location information unavailable.";
//               break;
//             case error.TIMEOUT:
//               errorMessage += "Request timed out.";
//               break;
//           }
//           showLocationStatus(errorMessage, "error");
//           getLocationBtn.textContent = "📍 Get My Current Location";
//           getLocationBtn.disabled = false;
//         },
//       );
//     });
//   }

//   function showLocationStatus(message, type) {
//     if (locationStatus) {
//       locationStatus.textContent = message;
//       locationStatus.className = "location-status location-" + type;
//     }
//   }

//   // FORM VALIDATION
//   const form = document.querySelector("form");
//   if (form) {
//     form.addEventListener("submit", (e) => {
//       const description = document.getElementById("description").value.trim();
//       const address = document.getElementById("address").value.trim();
//       const category = document.getElementById("category_id").value;
//       if (!category || !description || !address) {
//         e.preventDefault();
//         alert("Please fill in all required fields");
//       }
//     });
//   }
// });
