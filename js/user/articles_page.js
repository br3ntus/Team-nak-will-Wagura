document.addEventListener("DOMContentLoaded", function () {
  if (!window.UserData) return;

  const searchInput =
    document.querySelector(".search-input input") ||
    document.querySelector(".search-bar input");
  const filterPills = document.querySelectorAll(".pill");
  const articlesGrid = document.querySelector(".articles-grid");
  const resultsCountEl = document.querySelector(".results-count");
  const breedSelect = document.querySelector(".toolbar select");
  if (!articlesGrid) return;

  const articles = UserData.getArticles();

  function renderArticles(items) {
    articlesGrid.innerHTML = items
      .map((article) => {
        const tagLabel = article.category
          .replace(/-/g, " ")
          .replace(/\b\w/g, (c) => c.toUpperCase());

        return `
          <a href="article_single_page.html" class="article-card">
            <div class="article-header">
              <i class="fa-solid ${article.icon}"></i>
            </div>
            <div class="article-body">
              <div class="tag-row">
                <span class="tag ${article.category}">${tagLabel}</span>
                <span class="tag general">${article.breed}</span>
              </div>
              <h3>${article.title}</h3>
              <p>${article.summary}</p>
              <div class="article-footer">
                <span>${article.category.replace(/-/g, " ")} • ${article.readTime}</span>
                <span class="read-link">Read →</span>
              </div>
            </div>
          </a>
        `;
      })
      .join("");
  }

  function populateBreedSelect() {
    if (!breedSelect) return;
    const breeds = [
      "All breeds",
      ...new Set(articles.map((item) => item.breed)),
    ];
    breedSelect.innerHTML = breeds
      .map((breed) => `<option>${breed}</option>`)
      .join("");
  }

  function getCardTitle(card) {
    const h = card.querySelector("h3");
    return h && h.textContent ? h.textContent.toLowerCase() : "";
  }

  function getCardCategory(card) {
    const tag = card.querySelector(".tag-row .tag");
    return tag ? tag.textContent.trim() : "General";
  }

  function filterAndSearch() {
    const searchTerm = (searchInput?.value || "").toLowerCase();
    const selectedBreed = breedSelect?.value || "All breeds";
    let visibleCount = 0;
    const cards = articlesGrid.querySelectorAll(".article-card");

    cards.forEach((card) => {
      const title = getCardTitle(card);
      const category = getCardCategory(card);
      const matchesSearch = title.includes(searchTerm);
      const matchesFilter =
        currentFilter === "All" ||
        category.toLowerCase() === currentFilter.toLowerCase();
      const matchesBreed =
        selectedBreed === "All breeds" ||
        card.textContent.includes(selectedBreed);

      if (matchesSearch && matchesFilter && matchesBreed) {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (resultsCountEl)
      resultsCountEl.textContent = `${visibleCount} articles found`;
  }

  let currentFilter = "All";

  renderArticles(articles);
  populateBreedSelect();
  filterAndSearch();

  if (searchInput) searchInput.addEventListener("input", filterAndSearch);
  breedSelect?.addEventListener("change", filterAndSearch);

  filterPills.forEach((pill) => {
    pill.addEventListener("click", function () {
      filterPills.forEach((p) => p.classList.remove("active"));
      this.classList.add("active");
      currentFilter = this.textContent.trim();
      filterAndSearch();
    });
  });
});
