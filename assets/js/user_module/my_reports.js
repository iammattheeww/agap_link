document.addEventListener("DOMContentLoaded", function() {

       const modal = document.getElementById("reportModal");
        const openBtn = document.getElementById("openReportModal");
        const closeBtn = document.getElementById("closeReportModal");

        if (openBtn) {
            openBtn.addEventListener("click", function() {
                if (modal) modal.classList.add("active");
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener("click", function() {
                if (modal) modal.classList.remove("active");
            });
        }

        if (modal) {
            modal.addEventListener("click", function(e) {
                if (e.target === modal) {
                    modal.classList.remove("active");
                }
            });
        }

    });

const cancelBtn = document.getElementById("cancelReportBtn");
const createModal = document.getElementById("reportModal");

if (cancelBtn && createModal) {
    cancelBtn.addEventListener("click", function() {
        createModal.classList.remove("active");
    });
}

document.addEventListener("DOMContentLoaded", function() {

        const getLocationBtn = document.getElementById("getLocationBtn");
        const status = document.getElementById("locationStatus");
        const latInput = document.getElementById("gps_lat");
        const longInput = document.getElementById("gps_long");

        if (getLocationBtn) {
            getLocationBtn.addEventListener("click", function() {

                if (!navigator.geolocation) {
                    status.innerHTML = "Geolocation is not supported.";
                    return;
                }

                status.innerHTML = "Getting location...";

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latInput.value = position.coords.latitude;
                        longInput.value = position.coords.longitude;
                        status.innerHTML = "Location captured successfully.";
                    },
                    function() {
                        status.innerHTML = "Unable to retrieve location.";
                    }
                );
            });
        }

    });

document.addEventListener("DOMContentLoaded", function() {

        const fileUploadArea = document.getElementById("fileUploadArea");
        const fileInput = document.getElementById("photo");
        const previewContainer = document.getElementById("previewContainer");
        const previewImage = document.getElementById("previewImage");
        const removeBtn = document.getElementById("removeImageBtn");

        if (fileUploadArea) {
            fileUploadArea.addEventListener("click", () => fileInput.click());
        }

        if (fileInput) {
            fileInput.addEventListener("change", function() {
                const file = this.files[0];

                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = "block";
                };

                reader.readAsDataURL(file);
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener("click", function() {
                fileInput.value = "";
                previewContainer.style.display = "none";
            });
        }

    });
