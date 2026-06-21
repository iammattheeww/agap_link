// document.addEventListener("DOMContentLoaded", function () {
//   // THIS VARIABLE IS FOR THE CONDITIONAL STATEMENTS THAT OPENS THE MODAL
//   const modal = document.getElementById("reportModal");
//   const openBtn = document.getElementById("openReportModal");

//   // THIS VARIABLE IS FOR THE CONDITIONAL STATEMENTS THAT CLOSES THE MODAL
//   const closeBtn = document.getElementById("closeReportModal");
//   const cancelBtn = document.getElementById("cancelReportBtn");

//   // CLICK EVENT LISTENER FOR THE BUTTON
//   function openModal() {
//     if (modal) modal.classList.add("active");
//   }
//   function closeModal() {
//     if (modal) modal.classList.remove("active"); 
//   }

//   // OPENS THE MODAL
//   if (openBtn) openBtn.addEventListener("click", openModal); // CALLS THE openModal function -> openBtn is openModal()
//   if (closeBtn) closeBtn.addEventListener("click", closeModal); // CALLS THE closeModal function -> closeBtn is closeModal()
//   if (cancelBtn) cancelBtn.addEventListener("click", closeModal); // CALLS THE closeModal function -> cancelBtn is closeModal()
  
//   if (modal) {
//     modal.addEventListener("click", function (e) {
//       if (e.target === modal) closeModal();
//     });
//   }

//   // GEOLOCATION API
//   const getLocationBtn = document.getElementById("getLocationBtn");
//   const locationStatus = document.getElementById("locationStatus");
//   const latInput = document.getElementById("gps_lat");
//   const longInput = document.getElementById("gps_long");

//   if (getLocationBtn) {
//     getLocationBtn.addEventListener("click", function () {
//       if (!navigator.geolocation) {
//         if (locationStatus)
//           locationStatus.innerHTML = "Geolocation is not supported.";
//         return;
//       }
//       if (locationStatus) locationStatus.innerHTML = "Getting location...";
//       navigator.geolocation.getCurrentPosition(
//         function (position) {
//           if (latInput) latInput.value = position.coords.latitude;
//           if (longInput) longInput.value = position.coords.longitude;
//           if (locationStatus)
//             locationStatus.innerHTML = "✅ Location captured successfully.";
//         },
//         function () {
//           if (locationStatus)
//             locationStatus.innerHTML = "❌ Unable to retrieve location.";
//         },
//       );
//     });
//   }


//   // INITIALIZE LEAFLET MAP WHEN MODAL OPENS
//   if (openBtn) {
//     openBtn.addEventListener("click", function () {
//       setTimeout(() => {
//         if (typeof initReportMap === "function") initReportMap();
//       }, 150);
//     });
//   }
// });
