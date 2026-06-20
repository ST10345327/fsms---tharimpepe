<?php
/**
 * Fix the Beneficiaries table to add missing columns
 */
require_once __DIR__ . '/config/database.php';

$db = new Database();
$pdo = $db->connect();
if (!$pdo) die("Connection failed\n");

echo "Current Beneficiaries columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM beneficiaries");
$existingCols = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existingCols[$r['Field']] = $r['Type'];
    echo "  {$r['Field']} ({$r['Type']})\n";
}

$additions = [];

if (!isset($existingCols['FirstName'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN FirstName VARCHAR(100) NOT NULL AFTER BeneficiaryID");
    echo "\nAdded FirstName column.\n";
}

if (!isset($existingCols['LastName'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN LastName VARCHAR(100) NOT NULL AFTER FirstName");
    echo "Added LastName column.\n";
}

if (!isset($existingCols['Age'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Age INT DEFAULT NULL AFTER LastName");
    echo "Added Age column.\n";
}

if (!isset($existingCols['Gender'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Gender ENUM('Male','Female','Other') DEFAULT NULL AFTER Age");
    echo "Added Gender column.\n";
}

if (!isset($existingCols['Phone'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Phone VARCHAR(15) DEFAULT NULL AFTER Gender");
    echo "Added Phone column.\n";
}

if (!isset($existingCols['Email'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Email VARCHAR(100) DEFAULT NULL AFTER Phone");
    echo "Added Email column.\n";
}

if (!isset($existingCols['Address'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Address TEXT DEFAULT NULL AFTER Email");
    echo "Added Address column.\n";
}

if (!isset($existingCols['RegistrationDate'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN RegistrationDate DATE NOT NULL AFTER Address");
    echo "Added RegistrationDate column.\n";
}

// Rename STATUS to Status if needed
if (isset($existingCols['STATUS']) && !isset($existingCols['Status'])) {
    $pdo->exec("ALTER TABLE beneficiaries CHANGE COLUMN STATUS Status ENUM('active','inactive','suspended') DEFAULT 'active'");
    echo "Renamed STATUS to Status.\n";
} elseif (!isset($existingCols['STATUS']) && !isset($existingCols['Status'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Status ENUM('active','inactive','suspended') DEFAULT 'active' AFTER RegistrationDate");
    echo "Added Status column.\n";
}

if (!isset($existingCols['Notes'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN Notes TEXT DEFAULT NULL AFTER Status");
    echo "Added Notes column.\n";
}

if (!isset($existingCols['CreatedAt'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER Notes");
    echo "Added CreatedAt column.\n";
}

if (!isset($existingCols['UpdatedAt'])) {
    $pdo->exec("ALTER TABLE beneficiaries ADD COLUMN UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER CreatedAt");
    echo "Added UpdatedAt column.\n";
}

echo "\nFinal Beneficiaries table structure:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM beneficiaries");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']}) - Null: {$r['Null']}\n";
}

// Test the original Attendance query
echo "\nTesting Attendance query...\n";
try {
    $test = $pdo->query("SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate, a.Status, a.Notes,
                         b.FirstName, b.LastName, b.Age, b.Status as BeneficiaryStatus
                  FROM attendance a
                  LEFT JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  LIMIT 5");
    $rows = $test->fetchAll(PDO::FETCH_ASSOC);
    echo "Query succeeded. Found " . count($rows) . " rows.\n";
} catch (Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
}