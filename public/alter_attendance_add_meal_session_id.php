<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();
if (!$db) {
    die('Database connection failed');
}

$sql = "ALTER TABLE Attendance ADD COLUMN MealSessionID INT NULL";
try {
    $db->exec($sql);
    echo "Column MealSessionID added successfully.";
} catch (PDOException $e) {
    echo 'Error adding column: ' . $e->getMessage();
}
?>
