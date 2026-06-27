<?php
/**
 * Database Connection Config File
 * 
 * Uses PDO (PHP Data Objects) for secure, prepared SQL execution.
 * Standard credentials for local XAMPP environment.
 */

// Connection parameters
$host = 'localhost';
$db   = 'wagura_db';
$user = 'root';
$pass = ''; // Default empty password for XAMPP root
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
    PDO::ATTR_EMULATE_PREPARES   => false,                  
];

try {
    // Instantiate PDO connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, output error and halt
    die("Database connection failed: " . $e->getMessage());
}
