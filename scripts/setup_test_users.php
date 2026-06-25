<?php
/**
 * Setup Test Users for FSMS
 * Run: php scripts/setup_test_users.php
 * Creates test accounts for all roles with password 'password123'
 */

require_once __DIR__ . "/../config/database.php";

$pdo = getConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

$testUsers = [
    ['admin',       'admin@fsms.local',      'Admin User',       '1234567890', 'admin'],
    ['staff1',      'staff1@fsms.local',     'Staff One',        '1234567891', 'staff'],
    ['staff2',      'staff2@fsms.local',     'Staff Two',        '1234567892', 'staff'],
    ['volunteer1',  'volunteer1@fsms.local', 'Volunteer One',    '1234567893', 'volunteer'],
    ['volunteer2',  'volunteer2@fsms.local', 'Volunteer Two',    '1234567894', 'volunteer'],
    ['donor1',      'donor1@fsms.local',     'Donor One',        '1234567895', 'donor'],
    ['donor2',      'donor2@fsms.local',     'Donor Two',        '1234567896', 'donor'],
];

$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);
$created = 0;
$skipped = 0;

$stmt = $pdo->prepare("
    INSERT IGNORE INTO Users (Username, Email, PasswordHash, FullName, Phone, Role, Status, CreatedAt)
    VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
");

foreach ($testUsers as [$username, $email, $fullName, $phone, $role]) {
    $stmt->execute([$username, $email, $hash, $fullName, $phone, $role]);
    if ($stmt->rowCount() > 0) {
        echo "  CREATED: $username ($role) - $email\n";
        $created++;
    } else {
        echo "  SKIPPED: $username already exists\n";
        $skipped++;
    }
}

echo "\n--- Summary ---\n";
echo "Users created: $created\n";
echo "Users skipped (already exist): $skipped\n";
echo "\nPassword for all users: $password\n";
echo "\nLogin URLs:\n";
echo "  http://localhost:8000/AuthController.php?action=login\n";
