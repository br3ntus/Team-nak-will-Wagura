document.addEventListener("DOMContentLoaded", function () {
  if (!window.UserData) return;

  const searchInput = document.querySelector(".search-bar input");
  const filterPills = document.querySelectorAll(".pill");
  const insightsGrid = document.querySelector(".insights-grid");
  const resultsInfo = document.querySelector(".results-info");
  const featuredTitle = document.querySelector(".featured-insight-card h2");
  const featuredMeta = document.querySelector(".featured-meta");
  if (!insightsGrid) return;

  const insights = UserData.getInsights();

  function buildInsightCard(insight) {
    return `
      <div class="insight-card" data-category="${insight.category}">
        <div class="insight-icon"><i class="fa-solid ${
          insight.category === "cats"
            ? "fa-cat"
            : insight.category === "dogs"
              ? "fa-dog"
              : insight.category === "ph-guide"
                ? "fa-star"
                : "fa-paw"
        }"></i></div>
        <div class="insight-tag ${insight.category}">${insight.category
          .replace(/-/g, " ")
          .replace(/\b\w/g, (c) => c.toUpperCase())}</div>
        <h3>${insight.text}</h3>
        <div class="insight-footer">
          <span>${insight.posted}</span>
          <a href="#" class="read-link">Read →</a>
        </div>
      </div>
    `;
  }

  function renderInsights() {
    insightsGrid.innerHTML = insights.map(buildInsightCard).join("");
    updateCounts();
  }

  function renderFeaturedInsight() {
    const topInsight =
      insights.find((item) => item.status === "today") || insights[0];
    if (!topInsight || !featuredTitle || !featuredMeta) return;
    featuredTitle.textContent = topInsight.text;
    featuredMeta.textContent = `${topInsight.category.replace(/-/g, " ")} • Posted by Admin`;
  }

  function getCardTitle(card) {
    const h = card.querySelector("h3");
    return h && h.textContent ? h.textContent.toLowerCase() : "";
  }

  function getCardCategory(card) {
    return card.dataset.category || "General";
  }

  function updateCounts() {
    const visibleCards = Array.from(
      insightsGrid.querySelectorAll(".insight-card"),
    ).filter((card) => card.style.display !== "none");
    if (resultsInfo)
      resultsInfo.textContent = `${visibleCards.length} insights total`;
  }

  function filterAndSearch() {
    const searchTerm = (searchInput?.value || "").toLowerCase();
    const cards = insightsGrid.querySelectorAll(".insight-card");

    cards.forEach((card) => {
      const title = getCardTitle(card);
      const category = getCardCategory(card);
      const matchesSearch = title.includes(searchTerm);
      const matchesFilter =
        currentFilter === "All" ||
        category.toLowerCase() === currentFilter.toLowerCase();

      card.style.display = matchesSearch && matchesFilter ? "" : "none";
    });

    updateCounts();
  }

  let currentFilter = "All";

  renderInsights();
  renderFeaturedInsight();

  if (searchInput) searchInput.addEventListener("input", filterAndSearch);

  filterPills.forEach((pill) => {
    pill.addEventListener("click", function () {
      filterPills.forEach((p) => p.classList.remove("active"));
      this.classList.add("active");
      currentFilter = this.textContent.trim();
      filterAndSearch();
    });
  });
});
