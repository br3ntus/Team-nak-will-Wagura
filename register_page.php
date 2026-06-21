<?php
/**
 * User Registration Page
 * 
 * Shows registration form. Redirects to dashboard if already logged in.
 * Handled by register.php for database inserts.
 */
session_start();

// If already logged in, skip login page
if (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard_page.html");
    exit();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Wagura Signup</title>
    <link rel="stylesheet" href="template.css" />
    <link rel="stylesheet" href="css/register_page.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="js/register_page.js" defer></script>
  </head>
  <body>
    <!-- Top navigation bar -->
    <div class="navbar">
      <div class="nav-left">
        <img src="images/Wagura Logo 60x60.png" alt="Wagura Logo" />
        <span class="logo-text">Wagura</span>
      </div>

      <div class="nav-right">
        <a href="landing_page.html">← Back to home</a>
      </div>
    </div>

    <div class="container">
      <div class="card">
        <!-- The left side shows all the cool stuff users get for signing up -->
        <div class="left">
          <h5>GET STARTED FOR FREE</h5>
          <h1>Create your <span>Wagura</span> account</h1>
          <p>
            Join pet owners in Laguna who use Wagura to keep their dogs and cats
            healthy all year round.
          </p>

          <ul class="features">
            <li>
              <i class="fa-solid fa-paw"></i> Enroll up to 5 pets with unique
              IDs
            </li>
            <li>
              <i class="fa-solid fa-notes-medical"></i> Log health entries for
              each pet
            </li>
            <li>
              <i class="fa-solid fa-book-open"></i> Access PH-specific guides
              anytime
            </li>
            <li>
              <i class="fa-solid fa-lightbulb"></i> Daily pet insights on every
              login
            </li>
          </ul>

          <small>Free forever. No credit card needed.</small>
        </div>

        <!-- The right side is the actual registration form -->
        <div class="right">
          <h2>Create your account</h2>
          <p class="sub">Fill in your details to get started with Wagura</p>

          <?php if (isset($_GET['error'])): ?>
            <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
              <?php
                switch ($_GET['error']) {
                  case 'empty_fields':
                    echo "Please fill in all required fields.";
                    break;
                  case 'terms_not_accepted':
                    echo "You must agree to the Terms of Use and Privacy Policy.";
                    break;
                  case 'password_mismatch':
                    echo "Passwords do not match.";
                    break;
                  case 'password_too_short':
                    echo "Password must be at least 8 characters long.";
                    break;
                  case 'invalid_email':
                    echo "Please enter a valid email address.";
                    break;
                  case 'username_taken':
                    echo "Username is already taken. Choose another one.";
                    break;
                  case 'email_taken':
                    echo "Email is already registered. Choose another one or log in.";
                    break;
                  case 'database_error':
                    echo "A database error occurred. Please try again later.";
                    break;
                  default:
                    echo "An unknown error occurred. Please try again.";
                }
              ?>
            </div>
          <?php endif; ?>

          <form id="register-form" action="register.php" method="POST">
            <div class="row">
              <input
                type="text"
                id="first-name"
                name="first_name"
                placeholder="First name"
                required />
              <input
                type="text"
                id="last-name"
                name="last_name"
                placeholder="Last name"
                required />
            </div>

            <input
              type="email"
              id="email"
              name="email"
              placeholder="Email address"
              required />
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Username"
              required />

            <div class="row">
              <!-- We wrap the passwords so we can place the eye icon correctly inside -->
              <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required />
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
              </div>
              <div class="password-container">
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm password" required />
                <i class="fa-solid fa-eye toggle-password" id="toggleConfirmPassword"></i>
              </div>
            </div>

            <div class="checkbox-row">
              <input type="checkbox" id="agree" name="agree" required />
              <label for="agree">
                I agree to Wagura’s Terms of Use and Privacy Policy
              </label>
            </div>

            <button type="submit" id="submit-btn">Create account</button>

            <p class="login">
              Already have an account? <a href="login_page.php">Log in here</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
