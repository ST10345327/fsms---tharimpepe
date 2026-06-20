<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
$conn = getConnection();
if (!$conn) {
    echo "❌ No DB connection\n";
    exit(1);
}
try {
    $sql = "ALTER TABLE Users ADD COLUMN FullName VARCHAR(255) NULL AFTER Email";
    $conn->exec($sql);
    echo "✅ FullName column added successfully\n";
} catch (PDOException $e) {
    echo "❌ Error adding column: " . $e->getMessage() . "\n";
}
?>
