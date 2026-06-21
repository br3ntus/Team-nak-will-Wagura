<?php
/**
 * Admin Login Controller
 * 
 * Authenticates administrators by matching credentials against the admins table.
 * Supports both plaintext fallback (for dev/seeded databases) and secure BCRYPT hashes.
 */

// Start session
session_start();

// Include database connection (pointing to root folder)
require_once __DIR__ . '/../db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate fields are not empty
    if (empty($email) || empty($password)) {
        header("Location: admin_login_page.php?error=empty_fields");
        exit();
    }

    try {
        // Query admin by email
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        // Verify password (supports bcrypt hash and plaintext fallback for development seeds)
        if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
            
            // Password matches! Initialize admin session variables
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            // Redirect to admin dashboard
            header("Location: admin_dashboard.html");
            exit();
        } else {
            // Invalid email or password
            header("Location: admin_login_page.php?error=invalid_credentials");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Admin login failed: " . $e->getMessage());
        header("Location: admin_login_page.php?error=database_error");
        exit();
    }
} else {
    // Direct access redirect
    header("Location: admin_login_page.php");
    exit();
}
