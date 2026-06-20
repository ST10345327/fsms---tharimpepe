<?php
// migrations/20260619_add_missing_columns.php
// Ensure required columns exist in the database schema.
require_once __DIR__ . '/../config/database.php';

function columnExists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
    $stmt->execute([':col' => $column]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

$database = new Database();
$pdo = $database->connect();
if (!$pdo) {
    die('Database connection failed');
}

// Ensure Attendance.SessionDate exists
if (!columnExists($pdo, 'Attendance', 'SessionDate')) {
    $pdo->exec("ALTER TABLE `Attendance` ADD COLUMN `SessionDate` DATE NOT NULL AFTER `MealSessionID`;");
    echo "Added Attendance.SessionDate column.\n";
} else {
    echo "Attendance.SessionDate already exists.\n";
}

// Ensure Users.FullName exists
if (!columnExists($pdo, 'Users', 'FullName')) {
    $pdo->exec("ALTER TABLE `Users` ADD COLUMN `FullName` VARCHAR(255) DEFAULT NULL AFTER `PasswordHash`;");
    echo "Added Users.FullName column.\n";
} else {
    echo "Users.FullName already exists.\n";
}
?>
