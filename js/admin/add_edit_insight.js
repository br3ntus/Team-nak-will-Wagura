// ═══════════════════════════════════════════════════════════════════════════
// ADD/EDIT INSIGHT PAGE: Frontend form handler for creating new insights
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Manages the insight creation form with live preview and saves insights
// Flow: User fills form + selects category → Live preview updates → Publish
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Initialize page when DOM is ready
  function init() {
    setupInsightPreview();
    attachFormHandler();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // UI SYNC: Live Preview Update
  // ─────────────────────────────────────────────────────────────────────────
  // As user types in the insight textarea, the preview box updates in real-time
  function setupInsightPreview() {
    const textarea = document.getElementById("insight_text");
    const previewBox = document.querySelector(
      ".insight-preview-box .preview-placeholder-text",
    );
    if (!textarea || !previewBox) return;

    // Update preview text as user types
    const update = () => {
      const value = textarea.value.trim();
      previewBox.textContent =
        value || "Write your insight above to see a preview here.";
    };

    textarea.addEventListener("input", update);
    update();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // FORM SUBMISSION: Handle insight publish action
  // ─────────────────────────────────────────────────────────────────────────
  // Validates form fields → Builds insight object → Saves to AdminData
  function attachFormHandler() {
    const form = document.querySelector("form");
    if (!form) return;

    form.addEventListener("submit", (event) => {
      event.preventDefault();
      if (!window.AdminData) return;

      // ───────────────────────────────────────────────────────────────────
      // Step 1: Collect form values
      // ───────────────────────────────────────────────────────────────────
      const category = document.getElementById("insight_category").value;
      const iconEmoji = document.getElementById("icon_emoji").value.trim();
      const posted = document.getElementById("date_posted").value.trim();
      const text = document.getElementById("insight_text").value.trim();

      // ───────────────────────────────────────────────────────────────────
      // Step 2: Validate required fields
      // ───────────────────────────────────────────────────────────────────
      if (!category || !iconEmoji || !posted || !text) {
        alert(
          "Please choose a category, icon, date, and insight text before posting.",
        );
        return;
      }

      // ───────────────────────────────────────────────────────────────────
      // Step 3: Build insight object
      // ───────────────────────────────────────────────────────────────────
      const insight = {
        id: generateId("INS", window.AdminData.getInsights()),
        category,
        text,
        posted,
        status: normalizeStatus(posted),
      };

      // ───────────────────────────────────────────────────────────────────
      // Step 4: Save to AdminData (which auto-saves to localStorage)
      // ───────────────────────────────────────────────────────────────────
      window.AdminData.addInsight(insight);
      alert("Insight posted successfully. Redirecting to Manage Insights.");
      window.location.href = "manage_insights.html";
    });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HELPER: Determine if insight is for "today" or "published" based on date
  // ─────────────────────────────────────────────────────────────────────────
  function normalizeStatus(postedDate) {
    const today = new Date();
    const formattedToday = today.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric",
    });
    return postedDate === formattedToday ? "today" : "published";
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HELPER: Generate unique sequential ID (e.g., INS-0001, INS-0002)
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
