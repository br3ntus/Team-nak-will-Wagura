// ═══════════════════════════════════════════════════════════════════════════
// MANAGE INSIGHTS PAGE: Display and manage insights from AdminData
// ═══════════════════════════════════════════════════════════════════════════
// Purpose: Renders insight table dynamically, handles delete actions, and
//          keeps counts and search filters in sync with stored data
// Data Source: AdminData (from localStorage)
// ═══════════════════════════════════════════════════════════════════════════

(function () {
  "use strict";

  // Initialize page by rendering data and setting up event handlers
  function init() {
    renderInsightRows();
    attachDeleteHandlers();
    updateInsightCount();
    refreshSearch();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // TABLE RENDERING: Generate HTML table rows from stored insight data
  // ─────────────────────────────────────────────────────────────────────────
  // Fetches insights from AdminData and builds table with icons, text excerpts,
  // category pills, status tags, and action buttons (Edit/Delete)
  function renderInsightRows() {
    const tbody = document.querySelector(".admin-table tbody");
    if (!tbody || !window.AdminData) return;

    // Get all insights from AdminData (stored in localStorage)
    const insights = window.AdminData.getInsights();
    // Build HTML string for each insight row
    tbody.innerHTML = insights
      .map((insight) => {
        const icon = getCategoryIcon(insight.category);
        const categoryLabel = formatCategoryLabel(insight.category);
        const statusLabel =
          insight.status === "today"
            ? `<span class="status-pill today"><i class="fa-solid fa-star"></i> Today</span>`
            : `<span class="status-text-published">Published</span>`;
        return `
          <tr data-id="${escapeHtml(insight.id)}">
            <td>
              <div class="entity-cell">
                <div class="insight-icon-box">
                  <i class="fa-solid ${escapeHtml(icon)}"></i>
                </div>
                <div>
                  <strong>${escapeHtml(insight.text)}</strong>
                </div>
              </div>
            </td>
            <td><span class="status-pill ${escapeHtml(insight.category)}">${escapeHtml(categoryLabel)}</span></td>
            <td>${escapeHtml(insight.posted || "")}</td>
            <td>${statusLabel}</td>
            <td>
              <div class="actions-cell">
                <a href="add_edit_insight.html" class="btn-table">Edit</a>
                <button class="btn-table danger">Delete</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join("");
  }

  // ─────────────────────────────────────────────────────────────────────────
  // DELETE HANDLERS: Remove insight from table and AdminData on button click
  // ─────────────────────────────────────────────────────────────────────────
  function attachDeleteHandlers() {
    document
      .querySelectorAll(".admin-table .btn-table.danger")
      .forEach((button) => {
        button.addEventListener("click", (event) => {
          event.preventDefault();
          const row = button.closest("tr");
          if (!row) return;

          // Get insight text for confirmation message
          const text =
            row.querySelector("strong")?.textContent || "this insight";
          const insightId = row.dataset.id;

          // Ask user for confirmation before deleting
          if (confirm(`Delete ${text}? This is a frontend-only demo action.`)) {
            // Remove from AdminData (localStorage) and DOM
            if (insightId && window.AdminData) {
              window.AdminData.removeItem("insights", insightId);
            }
            row.remove();

            // Update UI after deletion
            updateInsightCount();
            refreshSearch();
          }
        });
      });
  }

  // ─────────────────────────────────────────────────────────────────────────
  // COUNTER UPDATE: Refresh visible insight count in toolbar
  // ─────────────────────────────────────────────────────────────────────────
  // Updates the "X insights total" count to reflect deletions or filters
  function updateInsightCount() {
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
  // HELPERS: Format display data (icons, labels, HTML escaping)
  // ─────────────────────────────────────────────────────────────────────────

  // Map category to Font Awesome icon class
  function getCategoryIcon(category) {
    return (
      {
        dogs: "fa-dog",
        cats: "fa-cat",
        "ph-guide": "fa-star",
        general: "fa-paw",
      }[category] || "fa-star"
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
