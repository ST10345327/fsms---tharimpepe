<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
$pdo = getConnection();
if (!$pdo) {
    echo "❌ No DB connection\n";
    exit;
}
$stmt = $pdo->prepare('SELECT UserID, Username, Email, PasswordHash FROM Users WHERE Username = ?');
$stmt->execute(['admin']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    echo "User record:\n";
    var_export($user);
    echo "\n";
} else {
    echo "Admin user not found in DB.\n";
}
?>
