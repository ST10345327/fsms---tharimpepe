<?php
require_once __DIR__ . "/../config/database.php";
$pdo = getConnection();
if (!$pdo) { die("No DB connection\n"); }
$stmt = $pdo->query("SELECT UserID, Username, Email, Role, Status, FullName, Phone FROM Users ORDER BY UserID");
echo str_pad("ID", 4) . str_pad("Username", 12) . str_pad("Role", 12) . str_pad("Status", 10) . "Name\n";
echo str_repeat("-", 60) . "\n";
foreach ($stmt as $u) {
    echo str_pad($u['UserID'], 4) . str_pad($u['Username'], 12) . str_pad($u['Role'], 12) . str_pad($u['Status'], 10) . ($u['FullName'] ?? $u['Username']) . "\n";
}
echo "\n" . str_repeat("=", 60) . "\n";
echo "Password for all test users: password123\n";
