// Page-specific JavaScript for admin/manage_pets.html
// This script enables frontend-only deletion of pet rows and count updates.
(function () {
  "use strict";

  function init() {
    attachDeleteHandlers();
    updatePetCount();
  }

  function attachDeleteHandlers() {
    document
      .querySelectorAll(".admin-table .btn-table.danger")
      .forEach((button) => {
        button.addEventListener("click", (event) => {
          event.preventDefault();
          const row = button.closest("tr");
          if (!row) return;
          const petName =
            row.querySelector("strong")?.textContent || "this pet";
          if (
            confirm(`Delete ${petName}? This is a frontend-only demo action.`)
          ) {
            row.remove();
            updatePetCount();
            refreshSearch();
          }
        });
      });
  }

  function updatePetCount() {
    const countNode = document.querySelector(".toolbar-right span");
    if (!countNode) return;
    const rows = document.querySelectorAll(
      ".admin-table tbody tr:not([style*='display: none'])",
    );
    countNode.textContent = `${rows.length}`;
  }

  function refreshSearch() {
    const input = document.querySelector(".admin-toolbar input[type='text']");
    if (input) {
      input.dispatchEvent(new Event("input"));
    }
  }

  document.addEventListener("DOMContentLoaded", init);
})();
