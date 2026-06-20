<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->connect();
if (!$pdo) {
    die("Connection failed\n");
}

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in database:\n";
foreach ($tables as $t) {
    echo "  - $t\n";
}

echo "\nMealSession columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM MealSession");
if ($stmt) {
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$r['Field']} ({$r['Type']})\n";
    }
} else {
    echo "  Table not found\n";
}

echo "\nTest query:\n";
try {
    $test = $pdo->query("SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate, a.Status, a.Notes, b.FirstName, b.LastName FROM Attendance a LEFT JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID LIMIT 5");
    $rows = $test->fetchAll(PDO::FETCH_ASSOC);
    echo "Query succeeded. Found " . count($rows) . " rows.\n";
    print_r($rows);
} catch (Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
}