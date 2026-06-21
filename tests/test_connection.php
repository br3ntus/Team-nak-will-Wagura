<?php
/**
 * Test Connection Script
 * 
 * Verifies that db_connection.php connects successfully and can query the database.
 */

// Include the connection file
require_once __DIR__ . '/../db_connection.php';

echo "=== Wagura DB Connection Test ===\n";

try {
    // 1. Test database connection
    if (isset($pdo)) {
        echo "SUCCESS: Connected to database using PDO.\n\n";
    } else {
        throw new Exception("PDO object is not set.");
    }

    // 2. Query users table
    echo "Querying 'users' table:\n";
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email, username FROM users");
    $users = $stmt->fetchAll();
    
    echo "Found " . count($users) . " user(s):\n";
    foreach ($users as $user) {
        echo "- ID: {$user['user_id']} | {$user['first_name']} {$user['last_name']} (Username: {$user['username']}, Email: {$user['email']})\n";
    }
    echo "\n";

    // 3. Query admins table
    echo "Querying 'admins' table:\n";
    $stmt = $pdo->query("SELECT admin_id, name, email FROM admins");
    $admins = $stmt->fetchAll();
    
    echo "Found " . count($admins) . " admin(s):\n";
    foreach ($admins as $admin) {
        echo "- ID: {$admin['admin_id']} | {$admin['name']} (Email: {$admin['email']})\n";
    }

    echo "\n=== Test Completed Successfully ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
