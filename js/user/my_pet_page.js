document.addEventListener("DOMContentLoaded", function () {
  if (!window.UserData) return;

  const petSearch = document.querySelector(".search-input-wrapper input");
  const selects = document.querySelectorAll(".filter-select");
  const breedFilter = selects[0] || null;
  const typeFilter = selects[1] || null;
  const petsGrid = document.querySelector(".pets-grid");
  const petCountLabel = document.querySelector(".pet-count-label");
  const slotText = document.querySelector(".slot-status-card .slot-text");
  const slotDots = document.querySelector(".slot-dots");
  if (!petsGrid) return;

  let pets = UserData.getPets();
  const capacity = UserData.getProfile().petCapacity || 5;

  function buildPetCard(pet) {
    const thumbContent = pet.photo 
      ? `<img src="${pet.photo}" alt="${pet.name}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;" />`
      : `<i class="fa-solid fa-${pet.type.toLowerCase() === "cat" ? "cat" : "dog"}"></i>`;

    return `
      <div class="pet-card" data-id="${pet.id}">
        <div class="pet-top-info">
          <div class="pet-thumb">${thumbContent}</div>
          <div class="pet-meta">
            <h3>${pet.name}</h3>
            <p>${pet.breed} • ${pet.type}</p>
            <small>ID: ${pet.id}</small>
          </div>
          <span class="status-badge">${pet.status}</span>
        </div>
        <div class="pet-stats">
          <div class="stat-box">
            <span class="stat-label">AGE</span>
            <span class="stat-val">${pet.age}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">WEIGHT</span>
            <span class="stat-val">${pet.weight}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">LAST LOG</span>
            <span class="stat-val">${pet.lastLog}</span>
          </div>
        </div>
        <div class="card-actions">
          <a href="#" class="action-btn logs"><i class="fa-solid fa-eye"></i> View logs</a>
          <a href="edit_pet_page.php?pet_code=${encodeURIComponent(pet.id)}" class="action-btn"><i class="fa-solid fa-pen"></i> Edit</a>
          <a href="#" class="action-btn delete"><i class="fa-solid fa-trash"></i> Delete</a>
        </div>
      </div>
    `;
  }

  function renderFilters() {
    if (breedFilter) {
      const breeds = ["All breeds", ...new Set(pets.map((pet) => pet.breed))];
      breedFilter.innerHTML = breeds
        .map((breed) => `<option>${breed}</option>`)
        .join("");
    }

    if (typeFilter) {
      const types = ["All types", ...new Set(pets.map((pet) => pet.type))];
      typeFilter.innerHTML = types
        .map((type) => `<option>${type}</option>`)
        .join("");
    }
  }

  function renderSlots() {
    const used = pets.length;
    if (slotText) {
      slotText.textContent = `Pet slots: ${used} of ${capacity} used. You can enroll ${Math.max(capacity - used, 0)} more pets.`;
    }
    if (slotDots) {
      slotDots.innerHTML = Array.from({ length: capacity })
        .map(
          (_, index) =>
            `<div class="dot ${index < used ? "active" : ""}"></div>`,
        )
        .join("");
    }
  }

  function renderPetList() {
    petsGrid.innerHTML =
      pets.map(buildPetCard).join("") +
      `
      <a href="add_pet_page.php" class="empty-slot-card">
        <i class="fa-solid fa-plus"></i>
        <span>Enroll a new pet</span>
        <p>${Math.max(capacity - pets.length, 0)} slots remaining</p>
      </a>
    `;
    filterPets();
  }

  function filterPets() {
    const searchTerm = (petSearch?.value || "").toLowerCase();
    const selectedBreed = breedFilter?.value || "All breeds";
    const selectedType = typeFilter?.value || "All types";
    let visibleCount = 0;

    petsGrid.querySelectorAll(".pet-card").forEach((card) => {
      const name = (card.querySelector("h3")?.textContent || "").toLowerCase();
      const meta = card.querySelector(".pet-meta p")?.textContent || "";
      const type = meta.split("•")[1]?.trim() || "";
      const breed = meta.split("•")[0]?.trim() || "";

      const matchesSearch = name.includes(searchTerm);
      const matchesBreed =
        selectedBreed === "All breeds" || breed === selectedBreed;
      const matchesType = selectedType === "All types" || type === selectedType;

      const show = matchesSearch && matchesBreed && matchesType;
      card.style.display = show ? "" : "none";
      if (show) visibleCount += 1;
    });

    if (petCountLabel)
      petCountLabel.textContent = `${visibleCount} pets enrolled`;
  }

  function deletePet(petId) {
    if (!confirm("Are you sure you want to delete this pet?")) return;

    // If backend is active, delete from database first, then re-render
    if (window.WaguraBackendData) {
      fetch(`/user/delete_pet.php?pet_code=${encodeURIComponent(petId)}`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Remove pet from in-memory data and re-render
            UserData.mock.pets = UserData.mock.pets.filter(p => p.id !== petId);
            pets = UserData.getPets();
            renderFilters();
            renderSlots();
            renderPetList();
          } else {
            alert("Could not delete pet: " + (data.error || "Unknown error."));
          }
        })
        .catch(err => {
          console.error("Delete failed:", err);
          alert("Network error. Could not delete pet.");
        });
      return;
    }

    // Fallback: localStorage mock
    UserData.removePet(petId);
    pets = UserData.getPets();
    renderFilters();
    renderSlots();
    renderPetList();
  }

  renderFilters();
  renderSlots();
  renderPetList();

  petSearch?.addEventListener("input", filterPets);
  breedFilter?.addEventListener("change", filterPets);
  typeFilter?.addEventListener("change", filterPets);

  petsGrid.addEventListener("click", function (event) {
    const deleteButton = event.target.closest(".action-btn.delete");
    if (!deleteButton) return;
    event.preventDefault();
    const petCard = deleteButton.closest(".pet-card");
    const petId = petCard?.dataset.id;
    if (petId) deletePet(petId);
  });
});
