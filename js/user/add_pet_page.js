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

  // ─────────────────────────────────────────────────────────────────────────
  // CANCEL BUTTON: Return to My Pets page without saving
  // ─────────────────────────────────────────────────────────────────────────
  cancelBtn?.addEventListener("click", function (event) {
    event.preventDefault();
    window.location.href = "my_pet_page.html";
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
    };

    // ───────────────────────────────────────────────────────────────────────
    // Step 2: Validate required fields (name, breed, age are mandatory)
    // ───────────────────────────────────────────────────────────────────────
    if (!petData.name || !petData.breed || !petData.age) {
      alert("Please enter the pet name, breed, and age before saving.");
      return;
    }

    // ───────────────────────────────────────────────────────────────────────
    // Step 3: Add pet to UserData (which auto-saves to localStorage)
    // ───────────────────────────────────────────────────────────────────────
    UserData.addPet(petData);
    alert("Pet saved successfully. Redirecting to My Pets.");
    window.location.href = "my_pet_page.html";
  });
});
