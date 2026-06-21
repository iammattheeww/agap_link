// document.addEventListener("DOMContentLoaded", function () {
//   // --- FILTER DROPDOWN ---
//   const filterDropdown = document.querySelector(".filter-dropdown");
//   const filterToggle   = document.querySelector(".filter-toggle");
//   const filterButtons  = document.querySelectorAll(".filter-btn");
//   const selectedFilter = document.getElementById("selectedFilter");
//   const reportCards    = document.querySelectorAll(".report-card");

//   if (selectedFilter) selectedFilter.textContent = "All";

//   if (filterToggle) {
//     filterToggle.addEventListener("click", function () {
//       filterDropdown && filterDropdown.classList.toggle("show");
//     });
//   }

//   document.addEventListener("click", function (e) {
//     if (filterDropdown && !filterDropdown.contains(e.target)) {
//       filterDropdown.classList.remove("show");
//     }
//   });

//   filterButtons.forEach(function (btn) {
//     btn.addEventListener("click", function () {
//       const filter = btn.dataset.filter;
//       if (selectedFilter) selectedFilter.textContent = filter.charAt(0).toUpperCase() + filter.slice(1);
//       filterButtons.forEach(b => b.classList.remove("active"));
//       btn.classList.add("active");
//       reportCards.forEach(function (card) {
//         if (filter === "all" || card.dataset.status === filter) {
//           card.classList.remove("hidden");
//           card.style.display = "";
//         } else {
//           card.classList.add("hidden");
//           card.style.display = "none";
//         }
//       });
//       if (filterDropdown) filterDropdown.classList.remove("show");
//     });
//   });

//   // --- REPORT CARD CLICK → MODAL WITH TIMELINE ---
//   const modal = document.getElementById("reportDetailModal");

//   reportCards.forEach(function (card) {
//     card.addEventListener("click", function () {
//       if (!modal) return;

//       document.getElementById("modalCategory").textContent   = this.dataset.category  || "";
//       document.getElementById("modalStatus").textContent     = this.dataset.statusText || "";
//       document.getElementById("modalAddress").textContent    = this.dataset.address    || "";
//       document.getElementById("modalDate").textContent       = this.dataset.date       || "";
//       document.getElementById("modalDescription").textContent = this.dataset.description || "";

//       // Timeline
//       const status        = (this.dataset.status || "").toLowerCase();
//       const submittedDate = this.dataset.date || "";

//       document.querySelectorAll(".timeline-item").forEach(item => item.classList.remove("active"));

//       let progressHeight = 0;
//       const step1 = document.querySelector('[data-step="1"]');
//       const step2 = document.querySelector('[data-step="2"]');
//       const step3 = document.querySelector('[data-step="3"]');
//       const tSub  = document.getElementById("timelineSubmitted");
//       const tOng  = document.getElementById("timelineOngoing");
//       const tRes  = document.getElementById("timelineResolved");
//       const tProg = document.getElementById("timelineProgress");

//       if (step1)  step1.classList.add("active");
//       if (tSub)   tSub.textContent = submittedDate;
//       progressHeight = 33;

//       if (status === "ongoing" || status === "forwarded" || status === "verified") {
//         if (step2) step2.classList.add("active");
//         if (tOng)  tOng.textContent = "In Progress";
//         progressHeight = 66;
//       }

//       if (status === "resolved") {
//         if (step2) step2.classList.add("active");
//         if (step3) step3.classList.add("active");
//         if (tOng)  tOng.textContent = "Processed";
//         if (tRes)  tRes.textContent = "Completed";
//         progressHeight = 100;
//       }

//       if (tProg) tProg.style.height = progressHeight + "%";

//      modal.style.display = "flex";

// // initialize map AFTER modal becomes visible
// setTimeout(() => {
//   initReportMap();
// }, 200);
//     });
//   });

//   // --- CLOSE MODAL ---
//   const closeModal = document.getElementById("closeModal");
//   if (closeModal) {
//     closeModal.addEventListener("click", function () {
//       if (modal) modal.style.display = "none";
//     });
//   }

//   if (modal) {
//     modal.addEventListener("click", function (e) {
//       if (e.target.id === "reportDetailModal") this.style.display = "none";
//     });
//   }

//   // --- SEARCH ---
//   const searchInput = document.getElementById("reportSearch");
//   if (searchInput) {
//     searchInput.addEventListener("input", function () {
//       const term = this.value.toLowerCase().trim();
//       reportCards.forEach(function (card) {
//         const cat  = (card.dataset.category    || "").toLowerCase();
//         const addr = (card.dataset.address     || "").toLowerCase();
//         const desc = (card.dataset.description || "").toLowerCase();
//         card.style.display = (!term || cat.includes(term) || addr.includes(term) || desc.includes(term)) ? "" : "none";
//       });
//     });
//   }
// });
// let reportMapInstance = null;

// function initReportMap() {
//   const mapEl = document.getElementById("reportMap");
//   if (!mapEl || typeof L === "undefined") return;

//   // already created
//   if (reportMapInstance) {
//     setTimeout(() => reportMapInstance.invalidateSize(), 200);
//     return;
//   }

//   const latInput = document.getElementById("gps_lat");
//   const lngInput = document.getElementById("gps_long");
//   const addressInput = document.getElementById("address");

//   const defaultLat = 11.5833;
//   const defaultLng = 122.75;

//   const map = L.map(mapEl).setView([defaultLat, defaultLng], 13);
//   reportMapInstance = map;

//   L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
//     attribution: "© OpenStreetMap"
//   }).addTo(map);

//   const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

//   function updateLocation(lat, lng) {
//     if (latInput) latInput.value = lat;
//     if (lngInput) lngInput.value = lng;

//     fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
//       .then(r => r.json())
//       .then(d => {
//         if (addressInput && d.display_name) addressInput.value = d.display_name;
//       });
//   }

//   marker.on("dragend", () => {
//     const p = marker.getLatLng();
//     updateLocation(p.lat, p.lng);
//   });

//   map.on("click", e => {
//     marker.setLatLng(e.latlng);
//     updateLocation(e.latlng.lat, e.latlng.lng);
//   });

//   if (navigator.geolocation) {
//     navigator.geolocation.getCurrentPosition(pos => {
//       const lat = pos.coords.latitude;
//       const lng = pos.coords.longitude;
//       map.setView([lat, lng], 17);
//       marker.setLatLng([lat, lng]);
//       updateLocation(lat, lng);
//     });
//   }

//   setTimeout(() => map.invalidateSize(), 200);
// }