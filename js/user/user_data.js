// ═══════════════════════════════════════════════════════════════════════════
// UserData: Mock user data storage using browser localStorage
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Provides a persistent data layer for the user dashboard without
//          requiring backend logic. All user data is stored in localStorage.
// Usage: Access via window.UserData after this file loads.
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Storage key for persisting user data in browser localStorage
  const STORAGE_KEY = "wagura_user_mock_data";

  // ─────────────────────────────────────────────────────────────────────────
  // INITIAL DATA: Default mock data structure with sample user records
  // ─────────────────────────────────────────────────────────────────────────
  // Contains: profile, pets, logs, articles, and insights collections
  // This data is used when localStorage is empty (first page load)
  const initialData = {
    profile: {
      name: "Brent",
      initials: "BA",
      petCapacity: 5,
    },
    pets: [
      {
        id: "WG-DOG-0001",
        name: "Coco",
        type: "Dog",
        breed: "Aspin",
        age: "2 yrs",
        weight: "8.5 kg",
        status: "Healthy",
        lastLog: "Today",
      },
      {
        id: "WG-CAT-0001",
        name: "Mochi",
        type: "Cat",
        breed: "Persian",
        age: "3 yrs",
        weight: "3.2 kg",
        status: "Healthy",
        lastLog: "Yesterday",
      },
      {
        id: "WG-DOG-0002",
        name: "Bruno",
        type: "Dog",
        breed: "Shih Tzu",
        age: "1 yr",
        weight: "5.1 kg",
        status: "Healthy",
        lastLog: "Mar 10",
      },
    ],
    logs: [
      {
        id: "LOG-0001",
        petId: "WG-DOG-0001",
        petName: "Coco",
        type: "Feeding",
        title: "Morning feeding",
        details: "200g dry food + water refilled",
        date: "Mar 20, 2026",
        time: "7:30 AM",
        categoryIcon: "fa-bowl-food",
      },
      {
        id: "LOG-0002",
        petId: "WG-CAT-0001",
        petName: "Mochi",
        type: "Weight",
        title: "Weight check",
        details: "3.2 kg recorded",
        date: "Mar 20, 2026",
        time: "9:00 AM",
        categoryIcon: "fa-weight-scale",
      },
      {
        id: "LOG-0003",
        petId: "WG-DOG-0002",
        petName: "Bruno",
        type: "Feeding",
        title: "Afternoon feeding",
        details: "150g wet food",
        date: "Mar 20, 2026",
        time: "12:00 PM",
        categoryIcon: "fa-bowl-food",
      },
      {
        id: "LOG-0004",
        petId: "WG-DOG-0002",
        petName: "Bruno",
        type: "Vet visit",
        title: "Annual checkup",
        details: "Dr. Santos, Laguna Animal Clinic — all clear",
        date: "Mar 19, 2026",
        time: "2:00 PM",
        categoryIcon: "fa-hospital",
      },
      {
        id: "LOG-0005",
        petId: "WG-DOG-0001",
        petName: "Coco",
        type: "Symptoms",
        title: "Scratching observed",
        details: "Scratching around neck area, possible ticks",
        date: "Mar 19, 2026",
        time: "6:00 PM",
        categoryIcon: "fa-face-frown",
      },
    ],
    articles: [
      {
        id: "ART-0001",
        title: "Protecting your dog from ticks in humid Laguna weather",
        category: "ph-guide",
        breed: "All breeds",
        summary:
          "Learn how to check for ticks and prevent infestations during the rainy season.",
        readTime: "3 min",
        posted: "Mar 10, 2026",
        icon: "fa-bug",
      },
      {
        id: "ART-0002",
        title: "Rabies prevention tips for Aspin owners near strays",
        category: "dogs",
        breed: "Aspin",
        summary:
          "Essential vaccination schedule and safety tips for dogs exposed to stray animals.",
        readTime: "4 min",
        posted: "Mar 8, 2026",
        icon: "fa-syringe",
      },
      {
        id: "ART-0003",
        title: "Keeping your cat cool during hot season in Laguna",
        category: "cats",
        breed: "All breeds",
        summary:
          "Tips for managing heat stress and keeping your cat hydrated during summer months.",
        readTime: "2 min",
        posted: "Mar 5, 2026",
        icon: "fa-temperature-high",
      },
      {
        id: "ART-0004",
        title: "What to do when your pet encounters a stray animal",
        category: "general",
        breed: "All breeds",
        summary:
          "Step by step guide on handling stray encounters safely in your neighborhood.",
        readTime: "5 min",
        posted: "Mar 3, 2026",
        icon: "fa-paw",
      },
    ],
    insights: [
      {
        id: "INS-0001",
        text: "Did you know? Cats sleep 12 to 16 hours a day to conserve energy for hunting.",
        category: "cats",
        posted: "Mar 20, 2026",
        status: "today",
      },
      {
        id: "INS-0002",
        text: "Aspins need extra deworming every 3 months due to outdoor exposure.",
        category: "dogs",
        posted: "Mar 19, 2026",
        status: "published",
      },
      {
        id: "INS-0003",
        text: "High humidity in Laguna can cause skin issues in pets. Check your pet daily.",
        category: "ph-guide",
        posted: "Mar 18, 2026",
        status: "published",
      },
      {
        id: "INS-0004",
        text: "Regular vet checkups should happen at least once a year for all pets.",
        category: "general",
        posted: "Mar 14, 2026",
        status: "published",
      },
    ],
  };

  const UserData = {
    load() {
      if (window.WaguraBackendData) {
        return window.WaguraBackendData;
      }
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        try {
          return JSON.parse(stored);
        } catch (error) {
          console.warn("UserData failed to parse stored JSON:", error);
        }
      }
      this.save(initialData);
      return this.clone(initialData);
    },

    save(data) {
      if (window.WaguraBackendData) {
        window.WaguraBackendData = data;
        return;
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    },

    reset() {
      localStorage.removeItem(STORAGE_KEY);
      return this.load();
    },

    clone(value) {
      return JSON.parse(JSON.stringify(value));
    },

    get mock() {
      if (!this._mock) {
        this._mock = this.load();
      }
      return this._mock;
    },

    set mock(value) {
      this._mock = value;
      this.save(value);
    },

    getProfile() {
      return this.clone(this.mock.profile);
    },

    getPetCapacity() {
      return this.mock.profile.petCapacity || 5;
    },

    canAddPet() {
      return this.getPets().length < this.getPetCapacity();
    },

    getPets() {
      return this.clone(this.mock.pets);
    },

    getLogs() {
      return this.clone(this.mock.logs);
    },

    getArticles() {
      return this.clone(this.mock.articles);
    },

    getInsights() {
      return this.clone(this.mock.insights);
    },

    getPetById(id) {
      return this.clone(this.mock.pets.find((pet) => pet.id === id) || null);
    },

    addPet(petData) {
      const mock = this.mock;
      const capacity = mock.profile.petCapacity || 5;
      if (mock.pets.length >= capacity) {
        return null;
      }

      const nextNumber = mock.pets.length + 1;
      const prefix =
        petData.type && petData.type.toLowerCase() === "cat"
          ? "WG-CAT"
          : "WG-DOG";
      const id = `${prefix}-${String(nextNumber).padStart(4, "0")}`;
      const pet = {
        id,
        name: petData.name,
        type: petData.type,
        breed: petData.breed,
        age: petData.age,
        weight: petData.weight || "-",
        status: "Healthy",
        lastLog: "No logs yet",
      };
      mock.pets.unshift(pet);
      this.mock = mock;
      return pet;
    },

    addLog(logData) {
      const mock = this.mock;
      const nextNumber = mock.logs.length + 1;
      const id = `LOG-${String(nextNumber).padStart(4, "0")}`;
      const pet =
        mock.pets.find((petItem) => petItem.id === logData.petId) || {};
      const log = {
        id,
        petId: logData.petId,
        petName: pet.name || "Unknown",
        type: logData.type,
        title: logData.title,
        details: logData.details,
        date:
          logData.date ||
          new Date().toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
          }),
        time:
          logData.time ||
          new Date().toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "2-digit",
          }),
        categoryIcon: logData.categoryIcon || "fa-bowl-food",
      };
      mock.logs.unshift(log);
      this.mock = mock;
      return log;
    },

    removePet(id) {
      const mock = this.mock;
      mock.pets = mock.pets.filter((pet) => pet.id !== id);
      mock.logs = mock.logs.filter((log) => log.petId !== id);
      this.mock = mock;

      // In backend mode, notify database asynchronously
      if (window.WaguraBackendData) {
        fetch(`delete_pet.php?pet_code=${id}`, { method: 'POST' })
          .then(res => res.json())
          .then(data => {
            if (!data.success) {
              console.error("Failed to delete pet from database:", data.error);
            }
          });
      }
    },
  };

  window.UserData = UserData;
})();
