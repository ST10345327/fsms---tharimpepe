<?php
// public/create_mealsession_table.php
// Migration script to create MealSession table for attendance tracking
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $pdo = $db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS MealSession (
        MealSessionID INT AUTO_INCREMENT PRIMARY KEY,
        SessionDate DATE NOT NULL,
        SessionType VARCHAR(50) NOT NULL,
        Location VARCHAR(100) NOT NULL,
        Notes TEXT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "✅ MealSession table created successfully\n";
} catch (Exception $e) {
    echo "❌ Error creating MealSession table: " . $e->getMessage() . "\n";
}
?>
