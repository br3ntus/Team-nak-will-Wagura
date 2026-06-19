// ═══════════════════════════════════════════════════════════════════════════
// ADD/EDIT ARTICLE PAGE: Frontend form handler for creating new articles
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Manages the article creation form and saves articles to AdminData
// Flow: User fills form → Click Publish → Save to localStorage → Redirect
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Initialize page when DOM is ready
  function init() {
    updateCategoryPreview();
    attachFormHandler();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // UI SYNC: Category Selection Preview
  // ─────────────────────────────────────────────────────────────────────────
  // Highlights the selected category pill as user picks category from dropdown
  function updateCategoryPreview() {
    const category = document.getElementById("category");
    const previewLabel = document.querySelector(".pills-preview-container");
    if (!category || !previewLabel) return;

    // Update active class on category pills when selection changes
    const update = () => {
      previewLabel.querySelectorAll(".status-pill").forEach((pill) => {
        pill.classList.toggle(
          "active",
          pill.classList.contains(category.value),
        );
      });
    };

    category.addEventListener("change", update);
    update();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // FORM SUBMISSION: Handle article publish action
  // ─────────────────────────────────────────────────────────────────────────
  // Validates form fields → Builds article object → Saves to AdminData
  function attachFormHandler() {
    const form = document.querySelector("form");
    if (!form) return;

    form.addEventListener("submit", (event) => {
      event.preventDefault();
      if (!window.AdminData) return;

      // ───────────────────────────────────────────────────────────────────
      // Step 1: Collect form values
      // ───────────────────────────────────────────────────────────────────
      const title = document.getElementById("title").value.trim();
      const content = document.getElementById("content").value.trim();
      const category = document.getElementById("category").value;
      const breed = document.getElementById("breed").value.trim();
      const iconEmoji = document.getElementById("icon_emoji").value.trim();
      const readTime =
        document.getElementById("read_time").value.trim() || "3 min";
      const status = document.getElementById("status").value || "published";

      // ───────────────────────────────────────────────────────────────────
      // Step 2: Validate required fields
      // ───────────────────────────────────────────────────────────────────
      if (!title || !content || !category) {
        alert(
          "Please fill in the article title, category, and content before publishing.",
        );
        return;
      }

      // ───────────────────────────────────────────────────────────────────
      // Step 3: Build article object (truncate content to summary)
      // ───────────────────────────────────────────────────────────────────
      const summary =
        content.length > 120 ? `${content.slice(0, 117).trim()}...` : content;
      const article = {
        id: generateId("ART", window.AdminData.getArticles()),
        title,
        category,
        breed: breed || "All breeds",
        readTime,
        posted: formatDate(new Date()),
        summary,
        content,
        icon: iconEmoji || "fa-pen-to-square",
        status,
      };

      // ───────────────────────────────────────────────────────────────────
      // Step 4: Save to AdminData (which auto-saves to localStorage)
      // ───────────────────────────────────────────────────────────────────
      window.AdminData.addArticle(article);
      alert("Article saved successfully. Redirecting to Manage Articles.");
      window.location.href = "manage_articles.html";
    });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HELPER: Format date as "Mar 21, 2026"
  // ─────────────────────────────────────────────────────────────────────────
  function formatDate(date) {
    return date.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HELPER: Generate unique sequential ID (e.g., ART-0001, ART-0002)
  // ─────────────────────────────────────────────────────────────────────────
  function generateId(prefix, existingItems) {
    const maxId = existingItems.reduce((max, item) => {
      const match = item.id && item.id.match(new RegExp(`^${prefix}-(\\d+)$`));
      if (!match) return max;
      return Math.max(max, Number(match[1]));
    }, 0);
    return `${prefix}-${String(maxId + 1).padStart(4, "0")}`;
  }

  document.addEventListener("DOMContentLoaded", init);
})();
