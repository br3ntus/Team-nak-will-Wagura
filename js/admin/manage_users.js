// Page-specific JavaScript for admin/manage_users.html
// This script adds frontend-only delete handling and live count updates
// so the manage users table behaves like a working admin prototype.
(function () {
  "use strict";

  function init() {
    attachDeleteHandlers();
    updateUserCount();
  }

  function attachDeleteHandlers() {
    document
      .querySelectorAll(".admin-table .btn-table.danger")
      .forEach((button) => {
        button.addEventListener("click", (event) => {
          event.preventDefault();
          const row = button.closest("tr");
          if (!row) return;
          const userName =
            row.querySelector("strong")?.textContent || "this user";
          if (
            confirm(`Delete ${userName}? This is a frontend-only demo action.`)
          ) {
            row.remove();
            updateUserCount();
            refreshSearch();
          }
        });
      });
  }

  function updateUserCount() {
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
