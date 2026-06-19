document.addEventListener("DOMContentLoaded", function () {
  if (!window.UserData) return;

  const profile = UserData.getProfile();
  const pets = UserData.getPets();
  const articles = UserData.getArticles();
  const insights = UserData.getInsights();

  document.querySelectorAll(".user-profile").forEach((el) => {
    el.textContent = profile.initials || "";
  });

  const greetingHeading = document.querySelector(".greeting-card h5");
  if (greetingHeading) {
    greetingHeading.textContent = `Good morning, ${profile.name}`;
  }

  const enrolledBadge = document.querySelector(".enrolled-badge");
  if (enrolledBadge) {
    enrolledBadge.innerHTML = `<i class="fa-solid fa-star"></i> ${pets.length} pets enrolled`;
  }

  const petList = document.querySelector(".pet-list");
  if (petList) {
    petList.innerHTML =
      pets
        .map((pet) => {
          const icon = pet.type.toLowerCase() === "cat" ? "cat" : "dog";
          return `
            <a href="add_pet_page.html" class="pet-card">
              <div class="pet-avatar">
                <i class="fa-solid fa-${icon}"></i>
              </div>
              <div class="pet-info">
                <h3>${pet.name}</h3>
                <p>${pet.breed} • ${pet.age}</p>
              </div>
              <div class="pet-id">${pet.id}</div>
              <span class="pet-status">${pet.status}</span>
            </a>
          `;
        })
        .join("") +
      `
        <a href="add_pet_page.html" class="add-pet-btn">
          <i class="fa-solid fa-plus"></i>
          <span>Add a new pet (${Math.max(profile.petCapacity - pets.length, 0)} slots remaining)</span>
        </a>
      `;
  }

  const articlesGrid = document.querySelector(".articles-grid");
  if (articlesGrid) {
    articlesGrid.innerHTML = articles
      .slice(0, 4)
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
              <div class="article-footer">
                <span>${article.breed} • ${article.readTime} read</span>
                <span class="read-link">Read →</span>
              </div>
            </div>
          </a>
        `;
      })
      .join("");
  }

  const insightList = document.querySelector(".insight-list");
  if (insightList) {
    insightList.innerHTML = insights
      .slice(0, 3)
      .map((insight) => {
        const icon =
          insight.category === "cats"
            ? "fa-cat"
            : insight.category === "dogs"
              ? "fa-dog"
              : insight.category === "ph-guide"
                ? "fa-star"
                : "fa-paw";
        return `
          <div class="insight-item">
            <div class="log-icon">
              <i class="fa-solid ${icon}"></i>
            </div>
            <div class="log-details">
              <h4>${insight.category.replace(/-/g, " ")}</h4>
              <p>${insight.text}</p>
            </div>
            <span class="log-time">${insight.posted}</span>
          </div>
        `;
      })
      .join("");
  }

  const searchInput = document.querySelector(".search-box input");
  if (searchInput && articlesGrid) {
    searchInput.addEventListener("input", function () {
      const query = this.value.trim().toLowerCase();
      articlesGrid.querySelectorAll(".article-card").forEach((card) => {
        const title = card.querySelector("h3")?.textContent.toLowerCase() || "";
        card.style.display = title.includes(query) ? "" : "none";
      });
    });
  }
});
