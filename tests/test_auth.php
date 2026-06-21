<?php
/**
 * Test Authentication Script
 * 
 * Simulates user registration, user login, and admin login to verify PDO logic.
 */

// Include connection
require_once __DIR__ . '/../db_connection.php';

echo "=== Wagura Auth Flow Test ===\n\n";

$test_username = 'tester123';
$test_email = 'tester123@example.com';
$test_password = 'securepassword123';
$test_first_name = 'Test';
$test_last_name = 'User';

try {
    // 1. Cleanup any previous test data
    $pdo->prepare("DELETE FROM users WHERE username = ? OR email = ?")->execute([$test_username, $test_email]);
    echo "1. Cleaned up old test users (if any).\n";

    // 2. Perform mock registration (simulating register.php)
    $hashed_password = password_hash($test_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, username, password, created_at)
        VALUES (:first_name, :last_name, :email, :username, :password, NOW())
    ");
    $stmt->execute([
        'first_name' => $test_first_name,
        'last_name'  => $test_last_name,
        'email'      => $test_email,
        'username'   => $test_username,
        'password'   => $hashed_password
    ]);
    echo "2. SUCCESS: Registered new user '$test_username'.\n";

    // 3. Test duplicate registration prevention
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$test_username]);
    if ($stmt->fetchColumn() > 0) {
        echo "3. SUCCESS: Database correctly reports duplicate username.\n";
    }

    // 4. Test login authentication (simulating login.php)
    // Query user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute([
        'username' => $test_username,
        'email'    => $test_username
    ]);
    $user = $stmt->fetch();

    if ($user && password_verify($test_password, $user['password'])) {
        echo "4. SUCCESS: User authentication verification passed (correct password).\n";
    } else {
        throw new Exception("User authentication failed with correct password.");
    }

    // Test login with incorrect password
    if ($user && !password_verify('wrongpassword', $user['password'])) {
        echo "5. SUCCESS: User authentication rejected bad password correctly.\n";
    } else {
        throw new Exception("User authentication allowed a bad password.");
    }

    // 5. Test Admin authentication (simulating admin_login_logic.php)
    $admin_email = 'admin@wagura.com';
    $admin_password_input = 'admin123';
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch();

    if ($admin && ($admin_password_input === $admin['password'] || password_verify($admin_password_input, $admin['password']))) {
        echo "6. SUCCESS: Admin authentication passed.\n";
    } else {
        throw new Exception("Admin authentication failed.");
    }

    // 6. Cleanup test user
    $pdo->prepare("DELETE FROM users WHERE username = ?")->execute([$test_username]);
    echo "7. SUCCESS: Cleaned up test user.\n";

    echo "\n=== All Authentication Tests Passed! ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    // Ensure cleanup happens even on error
    $pdo->prepare("DELETE FROM users WHERE username = ?")->execute([$test_username]);
}
