<?php
require_once 'db_connection_pdo.php';

echo "=== Database Structure Analysis ===\n\n";

try {
    // Get all tables
    $stmt = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'wagura_db'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found in wagura_db:\n";
    foreach ($tables as $table) {
        echo "\n--- TABLE: $table ---\n";
        
        // Get table structure
        $stmt = $conn->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            echo "  {$col['Field']} ({$col['Type']}) - Key: {$col['Key']}\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
