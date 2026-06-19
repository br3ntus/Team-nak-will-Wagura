// Standalone admin JavaScript (no PHP required)
// This file provides shared frontend-only admin behaviors that work across all
// admin pages. It is intentionally independent from backend logic.
(function () {
  "use strict";

  const Admin = {
    init() {
      this.setupConfirm();
      this.setupTableSearch();
      this.setupCategoryButtons();
      this.setupInsightPreview();
      this.setupArticlePreview();
    },

    // Attach click confirmation for delete-style buttons.
    // This is a frontend-only safeguard that works on any button or link
    // marked with [data-confirm] or .btn-table.danger.
    setupConfirm() {
      document
        .querySelectorAll("[data-confirm], .btn-table.danger")
        .forEach((el) => {
          el.addEventListener("click", (e) => {
            const msg =
              el.getAttribute("data-confirm") ||
              "Are you sure you want to delete this item?";
            if (!confirm(msg)) e.preventDefault();
          });
        });
    },

    // Provide client-side table search and filter behavior for admin tables.
    // This function will re-evaluate the table rows on each search/filter action,
    // so it works with tables that are rendered dynamically after page load.
    setupTableSearch() {
      document.querySelectorAll(".admin-toolbar").forEach((toolbar) => {
        const searchInput = toolbar.querySelector("input[type='text']");
        const filterSelect = toolbar.querySelector("select.filter-select");
        const countSpan = toolbar.querySelector(".toolbar-right span span");
        const table = toolbar
          .closest("section")
          ?.querySelector("table.admin-table");
        if (!table || !searchInput) return;

        const updateRows = () => {
          const rows = Array.from(table.querySelectorAll("tbody tr"));
          const query = searchInput.value.trim().toLowerCase();
          const filterValue = filterSelect?.value || "all";
          let visibleCount = 0;

          rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            const matchesQuery = !query || rowText.includes(query);
            const matchesFilter =
              filterValue === "all" ||
              rowText.includes(filterValue.toLowerCase());
            const show = matchesQuery && matchesFilter;
            row.style.display = show ? "" : "none";
            if (show) visibleCount += 1;
          });

          if (countSpan) {
            countSpan.textContent = visibleCount.toString();
          }
        };

        searchInput.addEventListener("input", updateRows);
        filterSelect?.addEventListener("change", updateRows);
        updateRows();
      });
    },

    // When category buttons are used on add/edit forms, keep a hidden field in sync
    // so the category selection behaves like a normal form control.
    setupCategoryButtons() {
      document.querySelectorAll(".category-btn-group").forEach((group) => {
        const hiddenInput = group
          .closest("form")
          ?.querySelector("#insight_category");
        group.addEventListener("click", (e) => {
          const btn = e.target.closest(".category-btn");
          if (!btn) return;
          group
            .querySelectorAll(".category-btn")
            .forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
          if (hiddenInput) {
            hiddenInput.value = btn.dataset.category || "";
          }
        });
      });
    },

    // Build a live preview for the add/edit insight page.
    setupInsightPreview() {
      const textarea = document.getElementById("insight_text");
      const previewBox = document.querySelector(
        ".insight-preview-box .preview-placeholder-text",
      );
      if (!textarea || !previewBox) return;
      const update = () => {
        const v = textarea.value.trim();
        previewBox.textContent =
          v || "Write your insight above to see a preview here.";
      };
      textarea.addEventListener("input", update);
      update();
    },

    // Highlight the selected article category pill on the add/edit article page.
    setupArticlePreview() {
      const category = document.getElementById("category");
      const previewLabel = document.querySelector(".pills-preview-container");
      if (!category || !previewLabel) return;

      const update = () => {
        const selectedCategory = category.value;
        previewLabel.querySelectorAll(".status-pill").forEach((pill) => {
          pill.classList.toggle(
            "active",
            pill.classList.contains(selectedCategory),
          );
        });
      };

      category.addEventListener("change", update);
      update();
    },
  };

  document.addEventListener("DOMContentLoaded", () => Admin.init());
  window.Admin = Admin;
})();
