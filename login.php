<?php
/**
 * User Login Controller
 * 
 * Authenticates users using username or email and passwords stored as secure BCRYPT hashes.
 * Starts user session upon successful authentication.
 */

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $username_or_email = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Validate inputs are not empty
    if (empty($username_or_email) || empty($password)) {
        header("Location: login_page.php?error=empty_fields");
        exit();
    }

    try {
        // 2. Query user by either username or email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            'username' => $username_or_email,
            'email'    => $username_or_email
        ]);
        $user = $stmt->fetch();

        // 3. Verify password if user exists
        if ($user && password_verify($password, $user['password'])) {
            // Password matches! Initialize session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // Generate initials (e.g. "Brent Aldhee" -> "BA")
            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
            $_SESSION['initials'] = $initials;

            // Handle Remember Me (optional/simple cookie setup)
            if (isset($_POST['remember'])) {
                // Set cookie for 30 days
                setcookie('wagura_username', $user['username'], time() + (86400 * 30), "/");
            } else {
                // Expire cookie
                if (isset($_COOKIE['wagura_username'])) {
                    setcookie('wagura_username', '', time() - 3600, "/");
                }
            }

            // Redirect to user dashboard
            header("Location: user/dashboard_page.html");
            exit();
        } else {
            // Invalid username or password
            header("Location: login_page.php?error=invalid_credentials");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Login failed: " . $e->getMessage());
        header("Location: login_page.php?error=database_error");
        exit();
    }
} else {
    // Direct access redirect
    header("Location: login_page.php");
    exit();
}
