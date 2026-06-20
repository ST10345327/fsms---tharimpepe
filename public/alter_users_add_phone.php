<?php
// Migration script to add Phone column to Users table
require_once __DIR__ . '/../app/helpers/bootstrap.php';
$conn = getConnection();
if (!$conn) {
    echo "❌ No DB connection\n";
    exit(1);
}
try {
    $sql = "ALTER TABLE Users ADD COLUMN Phone VARCHAR(20) NULL AFTER FullName";
    $conn->exec($sql);
    echo "✅ Phone column added successfully\n";
} catch (PDOException $e) {
    echo "❌ Error adding Phone column: " . $e->getMessage() . "\n";
}
?>
