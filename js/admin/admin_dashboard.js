// Page-specific JavaScript for admin/admin_dashboard.html
// This script reads mock admin data and updates dashboard counters
// so the dashboard feels interactive without backend logic.
(function () {
  "use strict";

  function init() {
    updateDashboardStats();
  }

  function updateDashboardStats() {
    if (!window.AdminData) return;

    const users = window.AdminData.getUsers();
    const pets = window.AdminData.getPets();
    const articles = window.AdminData.getArticles();
    const insights = window.AdminData.getInsights();

    setStatValue(".stat-card.users .stat-number", users.length);
    setStatValue(".stat-card.pets .stat-number", pets.length);
    setStatValue(".stat-card.articles .stat-number", articles.length);
    setStatValue(".stat-card.insights .stat-number", insights.length);
    setStatValue(".stat-card.logs .stat-number", calculateLogCount(pets));
  }

  function setStatValue(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
      element.textContent = value.toString();
    }
  }

  function calculateLogCount(pets) {
    return pets.reduce((sum, pet) => sum + Number(pet.logs || 0), 0);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
