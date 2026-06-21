// ═══════════════════════════════════════════════════════════════════════════
// AdminData: Mock admin data storage using browser localStorage
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Provides a persistent data layer for the admin dashboard without
//          requiring backend logic. All admin data is stored in localStorage.
// Usage: Access via window.AdminData after this file loads.
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Storage key for persisting admin data in browser localStorage
  const STORAGE_KEY = "wagura_admin_mock_data";

  // ─────────────────────────────────────────────────────────────────────────
  // INITIAL DATA: Default mock data structure with sample admin records
  // ─────────────────────────────────────────────────────────────────────────
  // Contains: users, pets, articles, and insights collections
  // This data is used when localStorage is empty (first page load)
  const initialData = {
    users: [
      {
        id: "USR-0001",
        name: "Brent Aldhee",
        email: "brent@email.com",
        status: "active",
        pets: 3,
        logs: 22,
        joined: "Mar 1, 2026",
      },
      {
        id: "USR-0002",
        name: "Juan dela Cruz",
        email: "juan@email.com",
        status: "new",
        pets: 1,
        logs: 4,
        joined: "Mar 18, 2026",
      },
      {
        id: "USR-0003",
        name: "Maria Lim",
        email: "maria@email.com",
        status: "active",
        pets: 5,
        logs: 38,
        joined: "Feb 14, 2026",
      },
      {
        id: "USR-0004",
        name: "Rico Cruz",
        email: "rico@email.com",
        status: "new",
        pets: 2,
        logs: 9,
        joined: "Mar 19, 2026",
      },
    ],
    pets: [
      {
        id: "PET-0001",
        name: "Coco",
        type: "Dog",
        breed: "Aspin",
        age: "2 yrs",
        weight: "8.5 kg",
        owner: "Brent A.",
        logs: 22,
        status: "healthy",
      },
      {
        id: "PET-0002",
        name: "Mochi",
        type: "Cat",
        breed: "Persian",
        age: "3 yrs",
        weight: "3.2 kg",
        owner: "Brent A.",
        logs: 15,
        status: "healthy",
      },
      {
        id: "PET-0003",
        name: "Bruno",
        type: "Dog",
        breed: "Shih Tzu",
        age: "1 yr",
        weight: "5.1 kg",
        owner: "Brent A.",
        logs: 9,
        status: "healthy",
      },
      {
        id: "PET-0004",
        name: "Niko",
        type: "Cat",
        breed: "Puspin",
        age: "2 yrs",
        weight: "2.8 kg",
        owner: "Juan D.",
        logs: 4,
        status: "healthy",
      },
    ],
    articles: [
      {
        id: "ART-0001",
        title: "Protecting your dog from ticks in humid Laguna weather",
        category: "ph-guide",
        breed: "All breeds",
        readTime: "3 min",
        posted: "Mar 10, 2026",
      },
      {
        id: "ART-0002",
        title: "Rabies prevention tips for Aspin owners near strays",
        category: "dogs",
        breed: "Aspin",
        readTime: "4 min",
        posted: "Mar 8, 2026",
      },
      {
        id: "ART-0003",
        title: "Keeping your cat cool during hot season in Laguna",
        category: "cats",
        breed: "All breeds",
        readTime: "2 min",
        posted: "Mar 5, 2026",
      },
      {
        id: "ART-0004",
        title: "What to do when your pet encounters a stray animal",
        category: "general",
        breed: "All breeds",
        readTime: "5 min",
        posted: "Mar 3, 2026",
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
    ],
  };

  // ─────────────────────────────────────────────────────────────────────────
  // AdminData Object: Main API for managing admin mock data
  // ─────────────────────────────────────────────────────────────────────────
  const AdminData = {
    // ───────────────────────────────────────────────────────────────────────
    // Data Persistence Methods
    // ───────────────────────────────────────────────────────────────────────

    // Loads admin data from localStorage, or uses initial data if not found
    load() {
      if (window.WaguraAdminBackendData) {
        return window.WaguraAdminBackendData;
      }
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        try {
          return JSON.parse(stored);
        } catch (error) {
          console.warn("AdminData failed to parse stored JSON:", error);
        }
      }
      this.save(initialData);
      return this.clone(initialData);
    },

    // Saves data to localStorage as JSON string
    save(data) {
      if (window.WaguraAdminBackendData) {
        window.WaguraAdminBackendData = data;
        return;
      }
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    },

    // Clears all stored data and reloads default initial data
    reset() {
      localStorage.removeItem(STORAGE_KEY);
      return this.load();
    },

    // Deep copies a value to prevent mutations (uses JSON parse/stringify trick)
    clone(value) {
      return JSON.parse(JSON.stringify(value));
    },

    // ───────────────────────────────────────────────────────────────────────
    // Data Access: Getters and Setters
    // ───────────────────────────────────────────────────────────────────────

    // Lazy-loads mock data into memory on first access
    get mock() {
      if (!this._mock) {
        this._mock = this.load();
      }
      return this._mock;
    },

    // Updates in-memory mock data and persists to localStorage
    set mock(value) {
      this._mock = value;
      this.save(value);
    },

    // ───────────────────────────────────────────────────────────────────────
    // Collection Getters: Return cloned data to prevent accidental mutations
    // ───────────────────────────────────────────────────────────────────────

    // Returns a deep copy of all users
    getUsers() {
      return this.clone(this.mock.users);
    },

    // Returns a deep copy of all pets
    getPets() {
      return this.clone(this.mock.pets);
    },

    // Returns a deep copy of all articles
    getArticles() {
      return this.clone(this.mock.articles);
    },

    // Returns a deep copy of all insights
    getInsights() {
      return this.clone(this.mock.insights);
    },

    // ───────────────────────────────────────────────────────────────────────
    // ID Generation: Auto-generate unique IDs for new items
    // ───────────────────────────────────────────────────────────────────────

    // Generates the next ID in sequence (e.g., ART-0005 after ART-0004)
    getNextId(prefix, collectionName) {
      const items = this.mock[collectionName] || [];
      const nextNumber = items.reduce((max, item) => {
        const match =
          item.id && item.id.match(new RegExp(`^${prefix}-(\\d+)$`));
        if (!match) return max;
        return Math.max(max, Number(match[1]));
      }, 0);
      return `${prefix}-${String(nextNumber + 1).padStart(4, "0")}`;
    },

    // ───────────────────────────────────────────────────────────────────────
    // CRUD Operations: Create, Read, Delete for admin items
    // ───────────────────────────────────────────────────────────────────────

    // Adds a new article to the beginning of the articles list
    // Auto-generates ID if not provided
    addArticle(article) {
      const mock = this.mock;
      if (!article.id) {
        article.id = this.getNextId("ART", "articles");
      }
      mock.articles.unshift(article); // Add to beginning for "latest first"
      this.mock = mock; // Save to localStorage
    },

    // Adds a new insight to the beginning of the insights list
    // Auto-generates ID if not provided
    addInsight(insight) {
      const mock = this.mock;
      if (!insight.id) {
        insight.id = this.getNextId("INS", "insights");
      }
      mock.insights.unshift(insight); // Add to beginning for "latest first"
      this.mock = mock; // Save to localStorage
    },

    // Deletes an item from the specified collection by ID
    removeItem(collection, id) {
      const mock = this.mock;
      mock[collection] = mock[collection].filter((item) => item.id !== id);
      this.mock = mock;
    },
  };

  window.AdminData = AdminData;
})();
