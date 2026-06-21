document.addEventListener("DOMContentLoaded", function () {
  // Check if we have edit data or need to parse from URL
  let editData = window.WaguraEditPet || null;
  const pet_code = editData ? editData.pet_code : new URLSearchParams(window.location.search).get("pet_code");

  if (!pet_code) {
    alert("No pet code specified.");
    window.location.href = "my_pet_page.php";
    return;
  }

  const nameInput = document.getElementById("edit-name");
  const breedInput = document.getElementById("edit-breed");
  const ageInput = document.getElementById("edit-age");
  const weightInput = document.getElementById("edit-weight");
  const sexSelect = document.getElementById("edit-sex");
  const colorInput = document.getElementById("edit-color");
  const notesInput = document.getElementById("edit-notes");
  const saveBtn = document.getElementById("edit-save-btn");

  const photoInput = document.getElementById("pet-photo-input");
  const photoPreview = document.getElementById("photo-preview");
  const photoIcon = document.getElementById("photo-icon");
  const photoLabel = document.getElementById("photo-label");

  // Fallback: If PHP backend didn't load the pet (or we're in mock mode), try loading from UserData (localStorage)
  if ((!editData || !editData.name) && window.UserData) {
    const localPet = window.UserData.getPetById(pet_code);
    if (localPet) {
      editData = {
        pet_code: localPet.id,
        name: localPet.name,
        type: localPet.type,
        breed: localPet.breed,
        age: localPet.age,
        weight: localPet.weight.replace(' kg', ''),
        sex: localPet.sex || '',
        color: localPet.color || '',
        medical_notes: localPet.notes || localPet.medical_notes || '',
        photo: localPet.photo || ''
      };

      // Fill inputs
      if (nameInput) nameInput.value = editData.name;
      if (breedInput) breedInput.value = editData.breed;
      if (ageInput) ageInput.value = editData.age;
      if (weightInput) weightInput.value = editData.weight === '-' ? '' : editData.weight;
      if (sexSelect) {
        if (editData.sex === "Male" || editData.sex === "Female") {
          sexSelect.value = editData.sex;
        } else {
          sexSelect.value = "Select sex";
        }
      }
      if (colorInput) colorInput.value = editData.color;
      if (notesInput) notesInput.value = editData.medical_notes;

      // Type active state
      document.querySelectorAll(".type-option").forEach(opt => {
        const optText = opt.textContent.trim().toLowerCase();
        if (optText === editData.type.toLowerCase()) {
          opt.classList.add("active");
        } else {
          opt.classList.remove("active");
        }
      });

      // Photo preview
      if (editData.photo && photoPreview) {
        photoPreview.src = editData.photo;
        photoPreview.style.display = "block";
        if (photoIcon) photoIcon.style.display = "none";
        if (photoLabel) photoLabel.style.display = "none";
      }
    } else {
      alert("Pet not found.");
      window.location.href = "my_pet_page.php";
      return;
    }
  }

  // Type selector toggle
  document.querySelectorAll(".type-option").forEach(opt => {
    opt.addEventListener("click", function () {
      document.querySelectorAll(".type-option").forEach(o => o.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // Photo preview on file select
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

  // Save changes
  saveBtn?.addEventListener("click", function () {
    const type = document.querySelector(".type-option.active")?.textContent?.trim() || "Dog";
    const name = nameInput?.value.trim();
    const breed = breedInput?.value.trim();
    const age = ageInput?.value.trim();
    const weight = weightInput?.value.trim();
    const sex = sexSelect?.value;
    const color = colorInput?.value.trim();
    const notes = notesInput?.value.trim();

    if (!name || !breed || !age) {
      alert("Please fill in pet name, breed, and age.");
      return;
    }

    // Backend DB save (check if DB data or profile name exists)
    const isBackendMode = window.WaguraBackendData && 
                          window.WaguraBackendData.profile && 
                          window.WaguraBackendData.profile.name;

    if (isBackendMode) {
      const payload = new FormData();
      payload.append("pet_code", pet_code);
      payload.append("type", type);
      payload.append("name", name);
      payload.append("breed", breed);
      payload.append("age", age);
      payload.append("weight", weight);
      payload.append("sex", sex);
      payload.append("color", color);
      payload.append("notes", notes);
      
      const photoFile = photoInput?.files[0];
      if (photoFile) {
        payload.append("photo", photoFile);
      }

      fetch("edit_pet_logic.php", { method: "POST", body: payload })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Pet updated successfully!");
            window.location.href = "my_pet_page.php";
          } else {
            alert("Error: " + (data.error || "Could not update pet."));
          }
        })
        .catch(err => {
          console.error(err);
          alert("Network error. Could not save changes.");
        });
      return;
    }

    // Mock storage update
    if (window.UserData) {
      const mock = window.UserData.mock;
      const petIndex = mock.pets.findIndex(p => p.id === pet_code);
      if (petIndex !== -1) {
        let finalPhoto = mock.pets[petIndex].photo || "";
        if (photoPreview && photoPreview.style.display !== "none" && photoPreview.src.startsWith("data:")) {
          finalPhoto = photoPreview.src;
        }

        mock.pets[petIndex] = {
          ...mock.pets[petIndex],
          name: name,
          type: type,
          breed: breed,
          age: age,
          weight: weight ? weight + " kg" : "-",
          sex: sex,
          color: color,
          notes: notes,
          photo: finalPhoto
        };
        window.UserData.mock = mock;
        alert("Pet updated successfully!");
        window.location.href = "my_pet_page.php";
      } else {
        alert("Pet not found in local storage.");
      }
    }
  });
});
