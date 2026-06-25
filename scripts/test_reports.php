<?php
/**
 * Test all report methods for errors and basic data accuracy
 */
require __DIR__ . '/../config/database.php';

$pdo = getConnection();
if (!$pdo) { die("No connection\n"); }

require __DIR__ . '/../app/models/Reports.php';
$reports = new Reports($pdo);

$pass = 0;
$fail = 0;

function test($name, $result, $check = null) {
    global $pass, $fail;
    if ($result === false || $result === null) {
        echo "  [FAIL] $name - returned false/null\n";
        $fail++;
    } elseif ($check && empty($result)) {
        echo "  [WARN] $name - returned empty set (may be OK if no data)\n";
        $pass++;
    } else {
        echo "  [PASS] $name - " . count($result) . " rows\n";
        $pass++;
    }
}

echo "=== Testing Report Methods ===\n\n";

echo "1. Attendance Report:\n";
test("no filters", $reports->getAttendanceReport());
test("with dates", $reports->getAttendanceReport('2026-01-01', '2026-12-31'));
test("with beneficiary", $reports->getAttendanceReport(null, null, 1));

echo "\n2. Donation Report:\n";
test("no filters", $reports->getDonationReport());
test("with dates", $reports->getDonationReport('2026-01-01', '2026-12-31'));
test("by type", $reports->getDonationReport(null, null, 'cash'));

echo "\n3. Donor Summary:\n";
test("all donors", $reports->getDonorSummaryReport());

echo "\n4. Food Stock Report:\n";
test("current stock", $reports->getFoodStockReport());

echo "\n5. Food Distribution Report:\n";
test("no filters", $reports->getFoodDistributionReport());
test("with dates", $reports->getFoodDistributionReport('2026-01-01', '2026-12-31'));

echo "\n6. Volunteer Performance Report:\n";
test("all volunteers", $reports->getVolunteerPerformanceReport());

echo "\n7. Volunteer Schedule Report:\n";
test("no filters", $reports->getVolunteerScheduleReport());
test("with dates", $reports->getVolunteerScheduleReport('2026-01-01', '2026-12-31'));
test("by status", $reports->getVolunteerScheduleReport(null, null, 'scheduled'));

echo "\n8. Beneficiary Report:\n";
test("no filter", $reports->getBeneficiaryReport());
test("by status", $reports->getBeneficiaryReport('active'));

echo "\n9. Activity Audit Report:\n";
test("no filters", $reports->getActivityAuditReport());
test("with dates", $reports->getActivityAuditReport('2026-01-01', '2026-12-31'));
test("by user", $reports->getActivityAuditReport(null, null, 1));
test("by action", $reports->getActivityAuditReport(null, null, null, 'Login'));

echo "\n10. Program Summary Report:\n";
test("no filters", $reports->getProgramSummaryReport());
test("with dates", $reports->getProgramSummaryReport('2026-01-01', '2026-12-31'));

echo "\n11. Financial Summary Report:\n";
$fin = $reports->getMonthlyFinancialSummary(2026, 6);
echo "  Keys: " . implode(', ', array_keys($fin)) . "\n";
$expectedKeys = ['period', 'total_income', 'total_expenses', 'donation_count', 'unique_donors', 'distribution_count', 'total_beneficiaries', 'total_meals', 'total_volunteer_hours'];
$missing = array_diff($expectedKeys, array_keys($fin));
if (empty($missing)) {
    echo "  [PASS] All expected keys present\n";
    $pass++;
} else {
    echo "  [FAIL] Missing keys: " . implode(', ', $missing) . "\n";
    $fail++;
}

echo "\n=== Summary: $pass passed, $fail failed ===\n";
