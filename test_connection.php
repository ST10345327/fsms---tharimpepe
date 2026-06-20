<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();
if ($pdo instanceof PDO) {
    echo "✅ Connection successful!\n";
    // Run a simple query
    $stmt = $pdo->query('SELECT 1');
    $result = $stmt->fetchColumn();
    echo "Test query result: $result\n";
} else {
    echo "❌ Failed to obtain PDO connection. Check error logs.\n";
}
?>
