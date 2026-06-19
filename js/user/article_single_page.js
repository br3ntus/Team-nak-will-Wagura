document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toc-link").forEach((link) => {
    link.addEventListener("click", function (event) {
      event.preventDefault();
      const targetHash = this.getAttribute("href");
      if (!targetHash || targetHash === "#") return;
      const targetElement = document.querySelector(targetHash);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
});
