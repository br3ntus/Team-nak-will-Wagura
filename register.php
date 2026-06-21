<?php
/**
 * User Registration Controller
 * 
 * Handles POST requests from the registration form, performs validation,
 * hashes passwords, and saves new user accounts to the database.
 */

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);

    // 1. Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: register_page.php?error=empty_fields");
        exit();
    }

    // 2. Validate Terms agreement
    if (!$agree) {
        header("Location: register_page.php?error=terms_not_accepted");
        exit();
    }

    // 3. Validate passwords match
    if ($password !== $confirm_password) {
        header("Location: register_page.php?error=password_mismatch");
        exit();
    }

    // 4. Validate password length
    if (strlen($password) < 8) {
        header("Location: register_page.php?error=password_too_short");
        exit();
    }

    // 5. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register_page.php?error=invalid_email");
        exit();
    }

    try {
        // 6. Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: register_page.php?error=username_taken");
            exit();
        }

        // 7. Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: register_page.php?error=email_taken");
            exit();
        }

        // 8. Hash password using secure BCRYPT algorithm
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 9. Insert new user into database
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, username, password, created_at)
            VALUES (:first_name, :last_name, :email, :username, :password, NOW())
        ");
        $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'username'   => $username,
            'password'   => $hashed_password
        ]);

        // Redirect to login page with success status
        header("Location: login_page.php?signup=success");
        exit();

    } catch (PDOException $e) {
        // Log the error and redirect with database error status
        error_log("Registration failed: " . $e->getMessage());
        header("Location: register_page.php?error=database_error");
        exit();
    }
} else {
    // If someone tries to access directly, redirect to register page
    header("Location: register_page.php");
    exit();
}
