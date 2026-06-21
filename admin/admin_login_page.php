<?php
/**
 * Admin Login Page
 * 
 * Restricted access login form. Redirects to admin dashboard if session exists.
 * Form submits to admin_login_logic.php.
 */
session_start();

// If already logged in as admin, go straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.html");
    exit();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Admin Login</title>
    <!-- We use the same brand colors and fonts from our template -->
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/admin_login_page.css" />
    <!-- Font Awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
    <!-- Top left brand area as seen in the design -->
    <header class="admin-login-nav">
      <div class="brand-group">
        <img
          src="../images/Wagura Logo 60x60.png"
          alt="W"
          class="brand-logo-img" />
        <span class="brand-name">Wagura</span>
      </div>
      <a href="../landing_page.html" class="back-home">← Back to home</a>
    </header>

    <main class="login-wrapper">
      <div class="login-split-card">
        <!-- Left Side: Information and Features -->
        <section class="info-side">
          <span class="access-tag">ADMIN ACCESS</span>
          <h1 class="main-heading">
            Manage Wagura from <span class="gold-text">here</span>
          </h1>
          <p class="intro-text">
            Access the admin dashboard to manage users, pets, articles, and
            daily insights.
          </p>

          <ul class="feature-list">
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-users"></i></div>
              <span>Manage all registered users</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle">
                <i class="fa-solid fa-pen-to-square"></i>
              </div>
              <span>Full CRUD for articles and insights</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-paw"></i></div>
              <span>Monitor enrolled pets across accounts</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <span>Moderate platform content</span>
            </li>
          </ul>

          <div class="footer-note">For authorized personnel only</div>
        </section>

        <!-- Right Side: The actual Login Form -->
        <section class="form-side">
          <h2>Admin Login</h2>
          <p class="sub-heading">Restricted access. Admins only.</p>

          <?php if (isset($_GET['error'])): ?>
            <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px;">
              <?php
                switch ($_GET['error']) {
                  case 'empty_fields':
                    echo "Please fill in all fields.";
                    break;
                  case 'invalid_credentials':
                    echo "Invalid email or password. Please try again.";
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

          <form action="admin_login_logic.php" method="POST">
            <div class="form-field">
              <label for="email">Email address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your admin email"
                required />
            </div>

            <div class="form-field">
              <label for="password">Password</label>
              <div class="password-box">
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="Enter your password"
                  required />
              </div>
            </div>

            <div class="form-meta">
              <label class="check-container">
                <input type="checkbox" name="remember" />
                <span class="check-label">Remember me</span>
              </label>
              <a href="#" class="gold-link">Forgot password?</a>
            </div>

            <button type="submit" class="admin-submit-btn">
              Log in to Admin
            </button>
          </form>

          <div class="restricted-bar">
            <i class="fa-solid fa-lock"></i>
            <span>This page is not publicly accessible</span>
          </div>
        </section>
      </div>
    </main>
    <script src="../js/admin/admin_shared.js"></script>
    <script src="../js/admin/admin_login_page.js"></script>
  </body>
</html>
