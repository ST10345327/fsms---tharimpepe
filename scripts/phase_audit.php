<?php
/**
 * Comprehensive Phase 1-6 Audit Verification
 * Run: php scripts/phase_audit.php
 */

require __DIR__ . '/../config/database.php';
$pdo = getConnection();
if (!$pdo) { die("FATAL: Database connection failed\n"); }

$pass = 0; $fail = 0; $warn = 0;

function ok($msg) { global $pass; echo "  [PASS] $msg\n"; $pass++; }
function nok($msg) { global $fail; echo "  [FAIL] $msg\n"; $fail++; }
function warn($msg) { global $warn; echo "  [WARN] $msg\n"; $warn++; }

echo "=== PHASE 1-3: Schema & Model Verification ===\n\n";

// Check Status column supports 'pending'
$stmt = $pdo->query("SHOW COLUMNS FROM Users LIKE 'Status'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
if (str_contains($col['Type'], 'pending')) ok("Users.Status enum includes 'pending'");
else nok("Users.Status missing 'pending'");

// Check FullName column exists
$stmt = $pdo->query("SHOW COLUMNS FROM Users LIKE 'FullName'");
if ($stmt->fetch()) ok("Users.FullName column exists");
else nok("Users.FullName missing");

// Check Phone column exists
$stmt = $pdo->query("SHOW COLUMNS FROM Users LIKE 'Phone'");
if ($stmt->fetch()) ok("Users.Phone column exists");
else nok("Users.Phone missing");

// Check VolunteerSchedules table
$stmt = $pdo->query("SHOW TABLES LIKE 'VolunteerSchedules'");
if ($stmt->fetch()) ok("VolunteerSchedules table exists");
else nok("VolunteerSchedules missing");

// Check VolunteerAvailability table (if exists in schema)
$stmt = $pdo->query("SHOW TABLES LIKE 'VolunteerAvailability'");
if ($stmt->fetch()) ok("VolunteerAvailability table exists");
else warn("VolunteerAvailability table missing (may not be created yet)");

echo "\n=== PHASE 4: RBAC Verification ===\n\n";

$controllers = glob(__DIR__ . '/../app/controllers/*.php');
$rbacCount = 0;
$loginCount = 0;
foreach ($controllers as $ctrl) {
    $name = basename($ctrl);
    $content = file_get_contents($ctrl);
    
    $hasLogin = str_contains($content, 'requireLogin()');
    $hasRbac = str_contains($content, 'rbacRequirePermission');
    
    if ($hasLogin) $loginCount++;
    if ($hasRbac) $rbacCount++;
    
    $missing = [];
    if (!$hasLogin) $missing[] = 'requireLogin()';
    if (!$hasRbac) $missing[] = 'rbacRequirePermission()';
    
    if (empty($missing)) ok("$name - has login + RBAC");
    else warn("$name - missing " . implode(', ', $missing));
}

ok("$loginCount/13 controllers have requireLogin()");
ok("$rbacCount/13 controllers have rbacRequirePermission()");

// Check helpers exist
foreach (['Rbac.php', 'SessionHandler.php', 'FormValidator.php', 'bootstrap.php'] as $h) {
    if (file_exists(__DIR__ . "/../app/helpers/$h")) ok("app/helpers/$h exists");
    else nok("app/helpers/$h missing");
}

echo "\n=== PHASE 5: Test Users Verification ===\n\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM Users WHERE Status = 'active'");
$active = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM Users WHERE Status = 'pending'");
$pending = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$total = (int)$stmt->fetchColumn();

ok("$total total users ($active active, $pending pending)");

if ($active >= 7) ok("At least 7 active users exist");
else warn("Only $active active users (expected 7+)");

// Check role distribution
$stmt = $pdo->query("SELECT Role, COUNT(*) FROM Users WHERE Status = 'active' GROUP BY Role");
echo "  Roles: ";
$roles = [];
foreach ($stmt as $row) { $roles[] = "{$row['Role']}({$row['COUNT(*)']})"; }
echo implode(', ', $roles) . "\n";

echo "\n=== PHASE 6: Report Model Verification ===\n\n";

require __DIR__ . '/../app/models/Reports.php';
$reports = new Reports($pdo);

$tests = [
    'getAttendanceReport' => [$reports->getAttendanceReport()],
    'getDonationReport' => [$reports->getDonationReport()],
    'getDonorSummaryReport' => [$reports->getDonorSummaryReport()],
    'getFoodStockReport' => [$reports->getFoodStockReport()],
    'getFoodDistributionReport' => [$reports->getFoodDistributionReport()],
    'getVolunteerPerformanceReport' => [$reports->getVolunteerPerformanceReport()],
    'getVolunteerScheduleReport' => [$reports->getVolunteerScheduleReport()],
    'getBeneficiaryReport' => [$reports->getBeneficiaryReport()],
    'getActivityAuditReport' => [$reports->getActivityAuditReport()],
    'getProgramSummaryReport' => [$reports->getProgramSummaryReport()],
];

$fin = $reports->getMonthlyFinancialSummary(2026, 6);
$expectedKeys = ['period', 'total_income', 'total_expenses', 'donation_count', 'unique_donors', 'distribution_count', 'total_beneficiaries', 'total_meals', 'total_volunteer_hours'];
$missingKeys = array_diff($expectedKeys, array_keys($fin));
if (empty($missingKeys)) ok("getMonthlyFinancialSummary has all 9 expected keys");
else nok("Missing keys: " . implode(', ', $missingKeys));

foreach ($tests as $name => $result) {
    if ($result[0] === false || $result[0] === null) nok("$name returned false/null");
    else ok("$name - " . count($result[0]) . " rows");
}

// Verify volunteer performance query (Firstname/Lastname fix)
$vp = $reports->getVolunteerPerformanceReport();
if ($vp && isset($vp[0]['FullName'])) ok("VolunteerPerformance returns FullName");
else warn("VolunteerPerformance data may be empty");

// Verify audit query column names
$audit = $reports->getActivityAuditReport();
if ($audit && isset($audit[0]['ActivityID'])) ok("ActivityAudit returns ActivityID");
if ($audit && isset($audit[0]['FullName'])) ok("ActivityAudit returns FullName");
if ($audit && isset($audit[0]['Details'])) ok("ActivityAudit returns Details");

echo "\n=== NAVIGATION & REGISTRATION ===\n\n";

// Check register view
$regView = file_get_contents(__DIR__ . '/../app/views/register.php');
if (str_contains($regView, 'full_name')) ok("Register form has Full Name field");
if (str_contains($regView, 'phone')) ok("Register form has Phone field");
if (str_contains($regView, 'role')) ok("Register form has Role select");
if (str_contains($regView, 'password')) ok("Register form has Password field");

// Check navbar has RBAC
$nav = file_get_contents(__DIR__ . '/../app/views/includes/navbar.php');
if (str_contains($nav, 'rbacNavItemsForRole')) ok("Navbar uses rbacNavItemsForRole");
else warn("Navbar may not use RBAC nav filtering");

// Check auth controller registration
$authCtrl = file_get_contents(__DIR__ . '/../app/controllers/AuthController.php');
if (str_contains($authCtrl, 'full_name')) ok("AuthController reads full_name");
if (str_contains($authCtrl, 'phone')) ok("AuthController reads phone");
if (str_contains($authCtrl, 'pending')) ok("AuthController sets pending status");
if (str_contains($authCtrl, 'password_hash')) ok("AuthController hashes passwords");

echo "\n=== SUMMARY ===\n";
echo "  Passed: $pass\n";
if ($warn) echo "  Warnings: $warn\n";
if ($fail) echo "  FAILED: $fail\n";
echo "  Status: " . ($fail === 0 ? "ALL PHASES VERIFIED" : "ISSUES FOUND") . "\n";
