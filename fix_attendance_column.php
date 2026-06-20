<?php
/**
 * Fix missing Status column in Attendance table
 */
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $pdo = $db->connect();
    
    if (!$pdo) {
        die("Database connection failed\n");
    }
    
    echo "Connected to database.\n";
    
    // Check existing columns in Attendance table
    $stmt = $pdo->query("SHOW COLUMNS FROM Attendance");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = $row['Type'];
        echo "  Column: {$row['Field']} ({$row['Type']})\n";
    }
    
    // Check if Status column exists
    if (!isset($columns['Status'])) {
        echo "\nStatus column is MISSING. Adding it now...\n";
        $pdo->exec("ALTER TABLE Attendance ADD COLUMN Status ENUM('present','absent','marked') DEFAULT 'present' AFTER SessionDate");
        echo "Status column added successfully!\n";
    } else {
        echo "\nStatus column already exists as: {$columns['Status']}\n";
    }
    
    // Also check if Notes column exists
    if (!isset($columns['Notes'])) {
        echo "\nNotes column is MISSING. Adding it now...\n";
        $pdo->exec("ALTER TABLE Attendance ADD COLUMN Notes TEXT AFTER Status");
        echo "Notes column added successfully!\n";
    }
    
    // Verify final structure
    echo "\nFinal Attendance table structure:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM Attendance");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Default']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}