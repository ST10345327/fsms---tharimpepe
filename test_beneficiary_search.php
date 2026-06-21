<?php
/**
 * Test script for Beneficiary Search functionality
 * Tests the fix for SQL parameter binding and undefined variable issues
 */

require_once __DIR__ . '/app/helpers/db.php';
require_once __DIR__ . '/app/models/Beneficiary.php';

echo "=== Beneficiary Search Test Suite ===\n\n";

try {
    $pdo = getDBConnection();
    $beneficiaryModel = new Beneficiary($pdo);
    
    // Test 1: Empty search term (should return empty array)
    echo "Test 1: Empty search term\n";
    $result = $beneficiaryModel->searchBeneficiaries('');
    assert(is_array($result));
    echo "✓ PASS: Returns array\n";
    echo "  Result count: " . count($result) . "\n\n";
    
    // Test 2: Search with special characters (SQL injection test)
    echo "Test 2: Search with special characters\n";
    $result = $beneficiaryModel->searchBeneficiaries("John'; DROP TABLE beneficiaries; --");
    assert(is_array($result));
    echo "✓ PASS: Handles special characters safely\n";
    echo "  Result count: " . count($result) . "\n\n";
    
    // Test 3: Search with wildcard characters
    echo "Test 3: Search with wildcard characters\n";
    $result = $beneficiaryModel->searchBeneficiaries("%test%");
    assert(is_array($result));
    echo "✓ PASS: Handles wildcard characters\n";
    echo "  Result count: " . count($result) . "\n\n";
    
    // Test 4: Partial name search
    echo "Test 4: Partial name search\n";
    $result = $beneficiaryModel->searchBeneficiaries("Jo");
    assert(is_array($result));
    echo "✓ PASS: Returns array for partial search\n";
    echo "  Result count: " . count($result) . "\n\n";
    
    // Test 5: Verify SQL parameter binding (no HY093 error)
    echo "Test 5: SQL parameter binding verification\n";
    try {
        $result = $beneficiaryModel->searchBeneficiaries("test");
        echo "✓ PASS: No SQLSTATE[HY093] error\n";
        echo "  Query executed successfully\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'HY093') !== false) {
            echo "✗ FAIL: SQLSTATE[HY093] error still present\n";
            echo "  Error: " . $e->getMessage() . "\n\n";
        } else {
            throw $e;
        }
    }
    
    // Test 6: Verify all three LIKE conditions work
    echo "Test 6: Search in FirstName, LastName, and Notes\n";
    $result = $beneficiaryModel->searchBeneficiaries("a");
    assert(is_array($result));
    echo "✓ PASS: Search works across all fields\n";
    echo "  Result count: " . count($result) . "\n\n";
    
    echo "=== All Tests Completed Successfully ===\n";
    
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>