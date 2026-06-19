document.addEventListener("DOMContentLoaded", function () {
  if (!window.UserData) return;

  const petSelector = document.querySelector(".pet-selector");
  const typeBtns = document.querySelectorAll(".type-btn");
  const dateInput = document.querySelector('input[value="March 20, 2026"]');
  const timeInput = document.querySelector('input[value="7:30 AM"]');
  const descInput =
    document.querySelector('input[placeholder*="e.g."]') ||
    document.getElementById("description");
  const notesInput = document.querySelector("textarea");
  const cancelBtn = document.querySelector(".cancel-btn");
  const saveBtn = document.querySelector(".save-btn");

  const labels = {
    Feeding: "Food description",
    Weight: "Weight (kg)",
    "Vet visit": "Vet visit description",
    Symptoms: "Symptom description",
  };
  const placeholders = {
    Feeding: "e.g. 200g dry food + water refilled",
    Weight: "e.g. 5.5 kg",
    "Vet visit": "e.g. Vaccination, check-up, etc.",
    Symptoms: "e.g. Coughing, vomiting, etc.",
  };

  const pets = UserData.getPets();

  function renderPetOptions() {
    if (!petSelector) return;
    petSelector.innerHTML = pets
      .map(
        (pet, index) => `
          <div class="pet-option ${index === 0 ? "active" : ""}">
            <i class="fa-solid fa-${pet.type.toLowerCase() === "cat" ? "cat" : "dog"}"></i> ${pet.name}
          </div>
        `,
      )
      .join("");
  }

  function getSelectedPet() {
    const activeOption = document.querySelector(".pet-option.active");
    const petName = activeOption?.textContent?.trim();
    return pets.find((pet) => pet.name === petName) || pets[0] || null;
  }

  function getSelectedType() {
    return (
      document.querySelector(".type-btn.active")?.textContent?.trim() ||
      "Feeding"
    );
  }

  function applyTypeDetails(type) {
    const descLabel = descInput?.closest(".form-group")?.querySelector("label");
    if (descLabel)
      descLabel.innerHTML = (labels[type] || "Details") + " <span>*</span>";
    if (descInput) descInput.placeholder = placeholders[type] || "";
  }

  function getDefaultLogTitle(type, petName) {
    return userUtils.getDefaultLogTitle(type, petName);
  }

  function getIconForType(type) {
    return userUtils.getLogIcon(type);
  }

  function addPetOptionListeners() {
    document.querySelectorAll(".pet-option").forEach((el) => {
      el.addEventListener("click", function () {
        document
          .querySelectorAll(".pet-option")
          .forEach((p) => p.classList.remove("active"));
        this.classList.add("active");
      });
    });
  }

  renderPetOptions();
  addPetOptionListeners();
  applyTypeDetails(getSelectedType());

  typeBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      typeBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      applyTypeDetails(getSelectedType());
    });
  });

  cancelBtn?.addEventListener("click", function (event) {
    event.preventDefault();
    window.location.href = "health_log_page.html";
  });

  saveBtn?.addEventListener("click", function (event) {
    event.preventDefault();
    const pet = getSelectedPet();
    const type = getSelectedType();
    const details = (descInput?.value || "").trim();
    const notes = (notesInput?.value || "").trim();

    if (!pet || !details) {
      alert("Please select a pet and enter the details before saving.");
      return;
    }

    const logData = {
      petId: pet.id,
      type,
      title: getDefaultLogTitle(type, pet.name),
      details: details + (notes ? ` — ${notes}` : ""),
      date: dateInput?.value?.trim() || "",
      time: timeInput?.value?.trim() || "",
      categoryIcon: getIconForType(type),
    };

    UserData.addLog(logData);
    alert("Health log saved successfully. Redirecting to Health Logs.");
    window.location.href = "health_log_page.html";
  });
});
