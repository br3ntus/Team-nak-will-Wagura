document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("register-form");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirm-password");

  // This part handles the "eye" icon so users can see what they're typing
  const setupToggle = (toggleId, inputId) => {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);
    toggle.addEventListener("click", () => {
      // We flip the input between 'password' and 'text'
      const type =
        input.getAttribute("type") === "password" ? "text" : "password";
      input.setAttribute("type", type);
      // And we swap the icon look
      toggle.classList.toggle("fa-eye");
      toggle.classList.toggle("fa-eye-slash");
    });
  };

  setupToggle("togglePassword", "password");
  setupToggle("toggleConfirmPassword", "confirm-password");

  // This runs when the user tries to submit the form
  form.addEventListener("submit", (e) => {
    // Here we check if the passwords match. If they don't, we stop the form.
    if (password.value !== confirmPassword.value) {
      e.preventDefault();
      alert("Error: Passwords do not match!");
      return;
    }

    // We also make sure the password is long enough to be secure
    if (password.value.length < 8) {
      e.preventDefault();
      alert("Error: Password must be at least 8 characters long.");
      return;
    }

    // We check if they agreed to the terms before letting them join
    const agree = document.getElementById("agree");
    if (!agree.checked) {
      e.preventDefault();
      alert("Error: You must agree to the Terms of Use.");
      return;
    }

    console.log("Registration form validated. Sending to PHP...");
  });
});
