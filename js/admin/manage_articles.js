// ═══════════════════════════════════════════════════════════════════════════
// MANAGE ARTICLES PAGE: Display and manage articles from AdminData
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Renders article table dynamically, handles delete actions, and
//          keeps counts and search filters in sync with stored data
// Data Source: AdminData (from localStorage)
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Timer for debouncing search refresh
  let followupUpdateTimer;

  // Initialize page by rendering data and setting up event handlers
  function init() {
    renderArticleRows();
    attachDeleteHandlers();
    updateArticleCount();
    refreshSearch();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // TABLE RENDERING: Generate HTML table rows from stored article data
  // ─────────────────────────────────────────────────────────────────────────
  // Fetches articles from AdminData and builds table with icons, summaries,
  // category pills, and action buttons (Edit/Delete)
  function renderArticleRows() {
    const tbody = document.querySelector(".admin-table tbody");
    if (!tbody || !window.AdminData) return;

    // Get all articles from AdminData (stored in localStorage)
    const articles = window.AdminData.getArticles();
    // Build HTML string for each article row
    tbody.innerHTML = articles
      .map((article) => {
        const icon = getCategoryIcon(article.category);
        const label = formatCategoryLabel(article.category);
        const summary = article.summary || "No summary available.";
        return `
          <tr data-id="${escapeHtml(article.id)}">
            <td>
              <div class="entity-cell">
                <div class="article-icon-box">
                  <i class="fa-solid ${escapeHtml(icon)}"></i>
                </div>
                <div>
                  <strong>${escapeHtml(article.title)}</strong>
                  <div style="color: #7f8ea0; font-size: 11px">
                    ${escapeHtml(summary)}
                  </div>
                </div>
              </div>
            </td>
            <td><span class="status-pill ${escapeHtml(article.category)}">${escapeHtml(label)}</span></td>
            <td>${escapeHtml(article.breed || "All breeds")}</td>
            <td>${escapeHtml(article.readTime || "3 min")}</td>
            <td>${escapeHtml(article.posted || "")}</td>
            <td>
              <div class="actions-cell">
                <a href="add_edit_article.html" class="btn-table">Edit</a>
                <button class="btn-table danger">Delete</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  // ─────────────────────────────────────────────────────────────────────────
  // DELETE HANDLERS: Remove article from table and AdminData on button click
  // ─────────────────────────────────────────────────────────────────────────
  function attachDeleteHandlers() {
    document
      .querySelectorAll(".admin-table .btn-table.danger")
      .forEach((button) => {
        button.addEventListener("click", (event) => {
          event.preventDefault();
          const row = button.closest("tr");
          if (!row) return;

          // Get article title for confirmation message
          const title =
            row.querySelector("strong")?.textContent || "this article";
          const articleId = row.dataset.id;

          // Ask user for confirmation before deleting
          const confirmed = confirm(
            `Delete ${title}? This is a frontend-only demo action.`,
          );
          if (!confirmed) return;

          // Remove from AdminData (localStorage) and DOM
          if (articleId && window.AdminData) {
            window.AdminData.removeItem("articles", articleId);
          }
          row.remove();

          // Update UI after deletion
          updateArticleCount();
          scheduleToolbarRefresh();
        });
      });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // COUNTER UPDATE: Refresh visible article count in toolbar
  // ─────────────────────────────────────────────────────────────────────────
  // Updates the "X articles total" count to reflect deletions or filters
  function updateArticleCount() {
    const countNode = document.querySelector(".toolbar-right span span");
    if (!countNode) return;
    // Count only visible rows (not hidden by search filter)
    const rows = document.querySelectorAll(
      ".admin-table tbody tr:not([style*='display: none'])",
    );
    countNode.textContent = rows.length.toString();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // SEARCH REFRESH: Trigger search filter to re-evaluate visibility
  // ─────────────────────────────────────────────────────────────────────────
  // Used after delete to re-apply search filters from admin_shared.js
  function refreshSearch() {
    const input = document.querySelector(".admin-toolbar input[type='text']");
    if (input) {
      // Dispatch input event to trigger shared search handler
      input.dispatchEvent(new Event("input"));
    }
  }

  // ─────────────────────────────────────────────────────────────────────────
  // DEBOUNCE HELPER: Delay search refresh to avoid rapid re-filtering
  // ─────────────────────────────────────────────────────────────────────────
  function scheduleToolbarRefresh() {
    window.clearTimeout(followupUpdateTimer);
    followupUpdateTimer = window.setTimeout(refreshSearch, 100);
  }

  // ─────────────────────────────────────────────────────────────────────────
  // HELPERS: Format display data (icons, labels, HTML escaping)
  // ─────────────────────────────────────────────────────────────────────────

  // Map category to Font Awesome icon class
  function getCategoryIcon(category) {
    return (
      {
        dogs: "fa-dog",
        cats: "fa-cat",
        "ph-guide": "fa-thermometer",
        general: "fa-paw",
      }[category] || "fa-pen-to-square"
    );
  }

  // Format category string to display label (e.g., "ph-guide" → "Ph Guide")
  function formatCategoryLabel(category) {
    return category.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
  }

  // Escape HTML special characters to prevent XSS vulnerabilities
  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  document.addEventListener("DOMContentLoaded", init);
})();
