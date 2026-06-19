document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const logCards = document.querySelectorAll(".log-entry-card");

  filterButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      filterButtons.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      const filterText = this.textContent.trim().toLowerCase();

      logCards.forEach((card) => {
        const typeLabel =
          card
            .querySelector(".log-type-label")
            ?.textContent?.trim()
            .toLowerCase() || "";
        if (
          filterText === "all logs" ||
          filterText === "all" ||
          typeLabel === filterText
        ) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });
    });
  });
});
