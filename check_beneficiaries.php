<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->connect();
if (!$pdo) die("Connection failed\n");

echo "Beneficiaries columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM beneficiaries");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']}) - Null: {$r['Null']} - Default: {$r['Default']}\n";
}