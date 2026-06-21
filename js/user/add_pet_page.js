// ═══════════════════════════════════════════════════════════════════════════
// ADD PET PAGE: Frontend form handler for adding new pets
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Manages the pet creation form and saves pets to UserData
// Flow: Select type → Fill details → Click Save → Save to localStorage → Redirect
// ═══════════════════════════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", function () {
  // Exit if UserData (from user_data.js) is not available
  if (!window.UserData) return;

  // ─────────────────────────────────────────────────────────────────────────
  // DOM SELECTORS: Cache form elements for performance
  // ─────────────────────────────────────────────────────────────────────────
  const typeOptions = document.querySelectorAll(".type-option");
  const cancelBtn = document.querySelector(".cancel-btn");
  const saveBtn = document.querySelector(".save-btn");
  const nameInput = document.querySelector('input[placeholder="e.g. Coco"]');
  const breedInput = document.querySelector(
    'input[placeholder="e.g. Aspin, Shih Tzu"]',
  );
  const ageInput = document.querySelector('input[placeholder="e.g. 2 years"]');
  const weightInput = document.querySelector('input[placeholder="e.g. 8.5"]');
  const sexSelect = document.querySelector("select");
  const colorInput = document.querySelector(
    'input[placeholder="e.g. Brown with white spots"]',
  );
  const notesInput = document.querySelector("textarea");
  
  // Photo selectors
  const photoInput = document.getElementById("pet-photo-input");
  const photoPreview = document.getElementById("photo-preview");
  const photoIcon = document.getElementById("photo-icon");
  const photoLabel = document.getElementById("photo-label");

  // ─────────────────────────────────────────────────────────────────────────
  // PHOTO PREVIEW: Display selected photo before upload
  // ─────────────────────────────────────────────────────────────────────────
  photoInput?.addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
      alert("Photo must be 2MB or less.");
      this.value = "";
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      if (photoPreview) {
        photoPreview.src = e.target.result;
        photoPreview.style.display = "block";
      }
      if (photoIcon) photoIcon.style.display = "none";
      if (photoLabel) photoLabel.style.display = "none";
    };
    reader.readAsDataURL(file);
  });

  // ─────────────────────────────────────────────────────────────────────────
  // PET TYPE SELECTION: Toggle active state on Dog/Cat buttons
  // ─────────────────────────────────────────────────────────────────────────
  typeOptions.forEach((opt) => {
    opt.addEventListener("click", function () {
      // Remove active from all type buttons
      typeOptions.forEach((o) => o.classList.remove("active"));
      // Add active to clicked button
      this.classList.add("active");
    });
  });

  // ─────────────────────────────────────────────────────────────────────────
  // HELPER: Get currently selected pet type (Dog or Cat)
  // ─────────────────────────────────────────────────────────────────────────
  const getSelectedType = () =>
    document.querySelector(".type-option.active")?.textContent?.trim() || "Dog";
  const hasPetSpace = () =>
    UserData.getPets().length < UserData.getProfile().petCapacity;

  const updateSaveBtn = () => {
    if (!saveBtn) return;
    if (!hasPetSpace()) {
      saveBtn.disabled = true;
      saveBtn.textContent = "Max pets reached";
    }
  };

  updateSaveBtn();
  // ─────────────────────────────────────────────────────────────────────────
  // CANCEL BUTTON: Return to My Pets page without saving
  // ─────────────────────────────────────────────────────────────────────────
  cancelBtn?.addEventListener("click", function (event) {
    event.preventDefault();
    window.location.href = "my_pet_page.php";
  });

  // ─────────────────────────────────────────────────────────────────────────
  // SAVE BUTTON: Validate form and save new pet to UserData
  // ─────────────────────────────────────────────────────────────────────────
  saveBtn?.addEventListener("click", function (event) {
    event.preventDefault();

    // ───────────────────────────────────────────────────────────────────────
    // Step 1: Collect all form field values
    // ───────────────────────────────────────────────────────────────────────
    const petData = {
      type: getSelectedType(),
      name: nameInput?.value.trim() || "",
      breed: breedInput?.value.trim() || "",
      age: ageInput?.value.trim() || "",
      weight: weightInput?.value.trim() || "",
      sex: sexSelect?.value || "",
      color: colorInput?.value.trim() || "",
      notes: notesInput?.value.trim() || "",
      photo: ""
    };

    // ───────────────────────────────────────────────────────────────────────
    // Step 2: Validate required fields (name, breed, age are mandatory)
    // ───────────────────────────────────────────────────────────────────────
    if (!petData.name || !petData.breed || !petData.age) {
      alert("Please enter the pet name, breed, and age before saving.");
      return;
    }

    if (!hasPetSpace()) {
      alert("You can only add up to 5 pets.");
      return;
    }

    // ───────────────────────────────────────────────────────────────────────
    // Step 3: Add pet to Database (if backend active) or local mock storage
    // ───────────────────────────────────────────────────────────────────────
    if (window.WaguraBackendData) {
      const payload = new FormData();
      payload.append("type", petData.type);
      payload.append("name", petData.name);
      payload.append("breed", petData.breed);
      payload.append("age", petData.age);
      payload.append("weight", petData.weight);
      payload.append("sex", petData.sex);
      payload.append("color", petData.color);
      payload.append("notes", petData.notes);
      
      const photoFile = photoInput?.files[0];
      if (photoFile) {
        payload.append("photo", photoFile);
      }

      fetch('add_pet_logic.php', {
        method: 'POST',
        body: payload
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Pet saved successfully. Redirecting to My Pets.");
          window.location.href = "my_pet_page.php";
        } else {
          alert("Error: " + (data.error || "Could not save pet."));
        }
      })
      .catch(err => {
        console.error(err);
        alert("Failed to submit pet details.");
      });
      return;
    }

    // Handle photo for local mock storage (base64)
    if (photoPreview && photoPreview.style.display !== "none" && photoPreview.src.startsWith("data:")) {
      petData.photo = photoPreview.src;
    }

    const addedPet = UserData.addPet(petData);
    if (!addedPet) {
      alert("You can only add up to 5 pets.");
      return;
    }

    alert("Pet saved successfully. Redirecting to My Pets.");
    window.location.href = "my_pet_page.php";
  });
});
