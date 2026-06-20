<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->connect();
if (!$pdo) die("Connection failed\n");

echo "Current Attendance columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM attendance");
$cols = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[$r['Field']] = $r['Type'];
    echo "  {$r['Field']} ({$r['Type']})\n";
}

if (!isset($cols['CreatedAt'])) {
    $pdo->exec("ALTER TABLE attendance ADD COLUMN CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER Notes");
    echo "\nAdded CreatedAt column.\n";
}

echo "\nFinal Attendance columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM attendance");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']})\n";
}

// Re-test the model
echo "\nRe-testing Attendance model...\n";
require_once __DIR__ . '/app/models/Attendance.php';
$att = new Attendance($pdo);
try {
    $data = $att->getAllAttendance();
    echo "SUCCESS: " . count($data) . " rows returned.\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}