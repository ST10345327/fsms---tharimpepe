<?php
/**
 * Module: Test Runner
 * Purpose: Run all tests and generate report
 * Reference: Test Automation Best Practices
 * Author: FSMS Development Agent
 */

// Initialize application
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/TestCase.php';

// Load all test files
$testDir = __DIR__;
$testFiles = glob($testDir . '/Test*.php');

echo "\n========================================\n";
echo "FSMS Test Suite Runner\n";
echo "========================================\n\n";

$totalRunner = new TestRunner();

foreach ($testFiles as $file) {
    // Skip TestCase.php itself
    if (basename($file) === 'TestCase.php') {
        continue;
    }
    
    echo "Loading: " . basename($file) . "\n";
    require_once $file;
}

// Discover and register test classes
$classes = get_declared_classes();
foreach ($classes as $class) {
    $reflection = new ReflectionClass($class);
    
    // Check if class extends TestCase and isn't abstract
    if ($reflection->isSubclassOf('TestCase') && !$reflection->isAbstract()) {
        // Check if it has test methods
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $hasTestMethods = false;
        
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $hasTestMethods = true;
                break;
            }
        }
        
        if ($hasTestMethods) {
            echo "Registering test class: $class\n";
            $totalRunner->addTest(new $class());
        }
    }
}

echo "\n========================================\n";
echo "Running Tests...\n";
echo "========================================\n";

// Run all tests
$results = $totalRunner->run();

// Print results
$totalRunner->printReport($results);

// Exit with status code
$exitCode = ($results['failedTests'] > 0) ? 1 : 0;
exit($exitCode);
?>
