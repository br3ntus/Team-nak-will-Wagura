// Common utilities for user pages
document.addEventListener("DOMContentLoaded", function () {
  window.qsAll = function (selector, root = document) {
    return Array.prototype.slice.call(
      (root || document).querySelectorAll(selector),
    );
  };

  window.userUtils = {
    formatDate(value) {
      if (!value) return "";
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) return value;
      return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      });
    },

    getLogIcon(type) {
      return (
        {
          Feeding: "fa-bowl-food",
          Weight: "fa-weight-scale",
          "Vet visit": "fa-hospital",
          Symptoms: "fa-face-frown",
        }[type] || "fa-paw"
      );
    },

    getDefaultLogTitle(type, petName) {
      return (
        {
          Feeding: `Feeding — ${petName}`,
          Weight: `Weight check — ${petName}`,
          "Vet visit": `Vet visit — ${petName}`,
          Symptoms: `Symptoms — ${petName}`,
        }[type] || `${type} — ${petName}`
      );
    },
  };
});
