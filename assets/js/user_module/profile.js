// FORM VALIDATION
document.getElementById("profileForm").addEventListener("submit", function (e) {
  const firstName = document.getElementById("first_name").value.trim();
  const lastName = document.getElementById("last_name").value.trim();
  const email = document.getElementById("email").value.trim();
  const phoneNumber = document.getElementById("phone_number").value.trim();

  if (!firstName || !lastName || !email || !phoneNumber) {
    e.preventDefault();
    alert("Please fill in all required fields.");
    return false;
  }

  // Validate phone number format
  const phonePattern = /^09[0-9]{9}$/;
  if (!phonePattern.test(phoneNumber)) {
    e.preventDefault();
    alert("Please enter a valid Philippine mobile number (09XXXXXXXXX)");
    return false;
  }
});

// // PASSWORD FORM VALIDATION
// document
//   .getElementById("passwordForm")
//   .addEventListener("submit", function (e) {
//     const currentPassword = document.getElementById("current_password").value;
//     const newPassword = document.getElementById("new_password").value;
//     const confirmPassword = document.getElementById("confirm_password").value;

//     if (!currentPassword || !newPassword || !confirmPassword) {
//       e.preventDefault();
//       alert("Please fill in all password fields.");
//       return false;
//     }

//     if (newPassword.length < 8) {
//       e.preventDefault();
//       alert("New password must be at least 8 characters long.");
//       return false;
//     }

//     if (newPassword !== confirmPassword) {
//       e.preventDefault();
//       alert("New passwords do not match.");
//       return false;
//     }
//   });

// RESET BUTTON HANDLER
document
  .querySelector(".btn-secondary")
  .addEventListener("click", function (e) {
    e.preventDefault();
    if (
      confirm(
        "Are you sure you want to cancel? All unsaved changes will be lost.",
      )
    ) {
      document.getElementById("profileForm").reset();
    }
  });

// DELETE MODAL FUNCTIONS
function openDeleteModal() {
  document.getElementById("deleteModal").style.display = "block";
}

function closeDeleteModal() {
  document.getElementById("deleteModal").style.display = "none";
  document.getElementById("delete_confirmation").value = "";
  document.getElementById("confirmDeleteBtn").disabled = true;
}

// ENABLE DELETE BUTTON ONLY WHEN "DELETE" IS TYPED
document
  .getElementById("delete_confirmation")
  .addEventListener("input", function () {
    const deleteBtn = document.getElementById("confirmDeleteBtn");
    if (this.value === "DELETE") {
      deleteBtn.disabled = false;
    } else {
      deleteBtn.disabled = true;
    }
  });

// CLOSE MODAL WHEN CLICKING OUTSIDE
window.onclick = function (event) {
  const modal = document.getElementById("deleteModal");
  if (event.target == modal) {
    closeDeleteModal();
  }
};
