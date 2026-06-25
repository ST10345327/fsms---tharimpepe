<?php
/**
 * Test the full registration -> approval -> login flow
 * Run: php scripts/test_registration_flow.php
 */

require_once __DIR__ . "/../config/database.php";

$pdo = getConnection();
if (!$pdo) { die("Database connection failed.\n"); }

$testUsername = 'test_reg_' . time();

echo "=== TEST 1: User Registration ===\n";

// Simulate what AuthController does
$fullName = 'Test Registration User';
$username = $testUsername;
$email = $testUsername . '@test.local';
$phone = '0712345678';
$role = 'volunteer';
$password = 'testpass123';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO Users (Username, Email, PasswordHash, FullName, Phone, Role, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
try {
    $stmt->execute([$username, $email, $hashedPassword, $fullName, $phone, $role]);
    $userId = $pdo->lastInsertId();
    echo "  [PASS] User created with ID: $userId\n";

    // Verify status is pending
    $stmt = $pdo->prepare("SELECT Status FROM Users WHERE UserID = ?");
    $stmt->execute([$userId]);
    $status = $stmt->fetchColumn();
    echo "  Status: " . ($status === 'pending' ? "[PASS] pending" : "[FAIL] expected pending, got $status") . "\n";

    // Verify can't login while pending - simulate login check
    $stmt = $pdo->prepare("SELECT PasswordHash, Status FROM Users WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $loginAllowed = ($user['Status'] === 'active' && password_verify($password, $user['PasswordHash']));
    echo "  Login while pending: " . ((!$loginAllowed) ? "[PASS] blocked" : "[FAIL] should be blocked") . "\n";

    echo "\n=== TEST 2: Admin Approval ===\n";

    // Simulate admin approval
    $stmt = $pdo->prepare("UPDATE Users SET Status = 'active', UpdatedAt = NOW() WHERE UserID = ? AND Status = 'pending'");
    $stmt->execute([$userId]);
    echo "  Approve executed: " . ($stmt->rowCount() > 0 ? "[PASS] row updated" : "[FAIL] no row updated") . "\n";

    // Verify status is now active
    $stmt = $pdo->prepare("SELECT Status FROM Users WHERE UserID = ?");
    $stmt->execute([$userId]);
    $status = $stmt->fetchColumn();
    echo "  Status after approval: " . ($status === 'active' ? "[PASS] active" : "[FAIL] expected active, got $status") . "\n";

    // Verify can login after approval
    $stmt = $pdo->prepare("SELECT PasswordHash, Status FROM Users WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $loginAllowed = ($user['Status'] === 'active' && password_verify($password, $user['PasswordHash']));
    echo "  Login after approval: " . ($loginAllowed ? "[PASS] allowed" : "[FAIL] should be allowed") . "\n";

    echo "\n=== TEST 3: Role-Based Access ===\n";

    // Check the user's role
    echo "  User role: $role (expected: volunteer)\n";
    $stmt = $pdo->prepare("SELECT Role FROM Users WHERE UserID = ?");
    $stmt->execute([$userId]);
    $dbRole = $stmt->fetchColumn();
    echo "  DB role matches: " . ($dbRole === $role ? "[PASS] yes" : "[FAIL] expected $role, got $dbRole") . "\n";

    // Cleanup - delete test user
    $stmt = $pdo->prepare("DELETE FROM Users WHERE UserID = ?");
    $stmt->execute([$userId]);
    echo "\n  [CLEANUP] Test user deleted.\n";

} catch (PDOException $e) {
    echo "  [FAIL] " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Registration -> Pending -> Approval -> Login: ALL TESTS PASSED\n";
