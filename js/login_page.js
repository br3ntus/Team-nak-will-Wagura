document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("login-form");

  // This part handles the "eye" icon so users can peek at their password
  const toggleBtn = document.getElementById("toggleLoginPassword");
  const passwordInput = document.getElementById("password");

  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener("click", () => {
      // We switch between hiding and showing the text
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      // And we update the icon look
      toggleBtn.classList.toggle("fa-eye");
      toggleBtn.classList.toggle("fa-eye-slash");
    });
  }

  // This runs when the user tries to log in
  form.addEventListener("submit", (e) => {
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    // We make sure they actually typed something in both boxes
    if (username === "" || password === "") {
      e.preventDefault();
      alert("Error: Please fill in all fields.");
      return;
    }

    console.log("Login form validated. Sending to PHP...");
  });

  // Here we check the URL to see if PHP sent back any alerts
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("error") === "invalid_credentials") {
    alert("Invalid username or password. Please try again.");
  }
  if (urlParams.get("signup") === "success") {
    alert("Account created successfully! You can now log in.");
  }
});
