<?php
// database/install.php
// Quick installer for standalone e-invoice database tables

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $conn->exec($sql);
    
    echo "Database schema installed successfully!\n";
    
    // Check tables
    $stmt = $conn->query("SHOW TABLES LIKE 'einv_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Created tables: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "Installation Error: " . $e->getMessage() . "\n";
}
