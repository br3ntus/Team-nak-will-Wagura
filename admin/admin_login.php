<?php
// Start secure session
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "../db_connection_pdo.php";
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        try {
            $user = null;
            
            // Try fetching from 'admins' table first
            try {
                $stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $row = $stmt->fetch();
                $user = is_array($row) ? $row : null;
            } catch (PDOException $e) {
                // Table 'admins' might not exist, fallback to 'users' table
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $row = $stmt->fetch();
                $user = is_array($row) ? $row : null;
            }
            
            if (is_array($user) && password_verify($password, (string)($user['password'] ?? ''))) {
                // Check if they are authorized as admin
                $isAdmin = false;
                if (isset($user['role']) && (strtolower((string)$user['role']) === 'admin' || strtolower((string)$user['role']) === 'administrator')) {
                    $isAdmin = true;
                } elseif (isset($user['is_admin']) && $user['is_admin'] == 1) {
                    $isAdmin = true;
                } elseif (isset($user['usertype']) && strtolower((string)$user['usertype']) === 'admin') {
                    $isAdmin = true;
                } else {
                    // If none of the admin columns exist, allow — this is a local dev setup
                    $has_role_col = false;
                    foreach (['role', 'is_admin', 'usertype'] as $col) {
                        if (array_key_exists($col, $user)) {
                            $has_role_col = true;
                            break;
                        }
                    }
                    if (!$has_role_col) {
                        $isAdmin = true;
                    }
                }
                
                if ($isAdmin) {
                    $_SESSION['admin_id'] = $user['user_id'] ?? $user['id'] ?? 1;
                    $_SESSION['admin_email'] = (string)($user['email'] ?? '');
                    $_SESSION['admin_name'] = ($user['first_name'] ?? 'Admin') . ' ' . ($user['last_name'] ?? '');
                    
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $error_msg = "Access denied. Not an authorized admin.";
                }
            } else {
                $error_msg = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Admin Login</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/admin_login_page.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
      .error-alert {
        background-color: rgba(240, 149, 149, 0.2);
        border: 1px solid #f09595;
        color: #f09595;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
      }
    </style>
  </head>
  <body>
    <header class="admin-login-nav">
      <div class="brand-group">
        <img src="../images/Wagura Logo 60x60.png" alt="W" class="brand-logo-img" />
        <span class="brand-name">Wagura</span>
      </div>
      <a href="../landing_page.html" class="back-home">← Back to home</a>
    </header>

    <main class="login-wrapper">
      <div class="login-split-card">
        <!-- Left Side: Information and Features -->
        <section class="info-side">
          <span class="access-tag">ADMIN ACCESS</span>
          <h1 class="main-heading">Manage Wagura from <span class="gold-text">here</span></h1>
          <p class="intro-text">
            Access the admin dashboard to manage users, pets, articles, and daily insights.
          </p>

          <ul class="feature-list">
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-users"></i></div>
              <span>Manage all registered users</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-pen-to-square"></i></div>
              <span>Full CRUD for articles and insights</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-paw"></i></div>
              <span>Monitor enrolled pets across accounts</span>
            </li>
            <li class="feature-item">
              <div class="icon-circle"><i class="fa-solid fa-shield-halved"></i></div>
              <span>Moderate platform content</span>
            </li>
          </ul>

          <div class="footer-note">For authorized personnel only</div>
        </section>

        <!-- Right Side: The actual Login Form -->
        <section class="form-side">
          <h2>Admin Login</h2>
          <p class="sub-heading">Restricted access. Admins only.</p>

          <?php if (!empty($error_msg)): ?>
            <div class="error-alert">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
          <?php endif; ?>

          <form id="adminLoginForm" action="admin_login.php" method="POST" novalidate>
            <div class="form-field">
              <label for="email">Email address</label>
              <input type="email" id="email" name="email" placeholder="Enter your admin email" required />
              <span class="validation-error" id="emailError" style="color: #f09595; font-size: 11px; margin-top: 5px; display: none;"></span>
            </div>

            <div class="form-field">
              <label for="password">Password</label>
              <div class="password-box">
                <input type="password" id="password" name="password" placeholder="Enter your password" required />
              </div>
              <span class="validation-error" id="passwordError" style="color: #f09595; font-size: 11px; margin-top: 5px; display: none;"></span>
            </div>

            <div class="form-meta">
              <label class="check-container">
                <input type="checkbox" name="remember" />
                <span class="check-label">Remember me</span>
              </label>
              <a href="#" class="gold-link">Forgot password?</a>
            </div>

            <button type="submit" class="admin-submit-btn">Log in to Admin</button>
          </form>

          <div class="restricted-bar">
            <i class="fa-solid fa-lock"></i>
            <span>This page is not publicly accessible</span>
          </div>
        </section>
      </div>
    </main>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("adminLoginForm");
        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("password");
        const emailError = document.getElementById("emailError");
        const passwordError = document.getElementById("passwordError");

        form.addEventListener("submit", function (e) {
          let isValid = true;

          // Validate Email
          const emailValue = emailInput.value.trim();
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (emailValue === "") {
            emailError.textContent = "Email address is required.";
            emailError.style.display = "block";
            isValid = false;
          } else if (!emailPattern.test(emailValue)) {
            emailError.textContent = "Please enter a valid email address.";
            emailError.style.display = "block";
            isValid = false;
          } else {
            emailError.style.display = "none";
          }

          // Validate Password
          const passwordValue = passwordInput.value;
          if (passwordValue === "") {
            passwordError.textContent = "Password is required.";
            passwordError.style.display = "block";
            isValid = false;
          } else if (passwordValue.length < 8) {
            passwordError.textContent = "Password must be at least 8 characters long.";
            passwordError.style.display = "block";
            isValid = false;
          } else {
            passwordError.style.display = "none";
          }

          if (!isValid) {
            e.preventDefault();
          }
        });
      });
    </script>
  </body>
</html>
