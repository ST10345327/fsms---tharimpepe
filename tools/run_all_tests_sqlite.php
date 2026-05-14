<?php
// Lightweight test runner that runs each Test* class using SQLite in-memory.
putenv('FSMS_TEST_SQLITE=1');
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../tests/TestCase.php';

$testDir = __DIR__ . '/../tests';
$testFiles = glob($testDir . '/Test*.php');

foreach ($testFiles as $file) {
    if (basename($file) === 'TestCase.php') continue;
    require_once $file;
}

$classes = get_declared_classes();
$testClasses = [];
foreach ($classes as $class) {
    $ref = new ReflectionClass($class);
    if ($ref->isSubclassOf('TestCase') && !$ref->isAbstract()) {
        $testClasses[] = $class;
    }
}

echo "Running all tests (SQLite in-memory)\n";
$summary = [ 'totalTests' => 0, 'passedTests' => 0, 'failedTests' => 0, 'totalAssertions' => 0, 'results' => [] ];

foreach ($testClasses as $class) {
    echo "\n--- CLASS: {$class} ---\n";
    $test = new $class();
    $ref = new ReflectionClass($test);
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        if (strpos($method->getName(), 'test') !== 0) continue;
        $testName = $class . '::' . $method->getName();
        echo "--> RUNNING: {$testName}\n";
        if (function_exists('flush')) { @flush(); }

        $test->resetAssertions();
        $summary['totalTests']++;

        try {
            $test->setUp();
            $method->invoke($test);
            $summary['passedTests']++;
            $summary['results'][] = ['test' => $testName, 'status' => 'PASS', 'assertions' => $test->getAssertionCount()];
            echo "    COMPLETED: {$testName}\n";
        } catch (AssertionException $e) {
            $summary['failedTests']++;
            $summary['results'][] = ['test' => $testName, 'status' => 'FAIL', 'error' => $e->getMessage(), 'assertions' => $test->getAssertionCount()];
            echo "    FAILURE: {$testName} - " . $e->getMessage() . "\n";
        } catch (Exception $e) {
            $summary['failedTests']++;
            $summary['results'][] = ['test' => $testName, 'status' => 'ERROR', 'error' => $e->getMessage(), 'assertions' => $test->getAssertionCount()];
            echo "    ERROR: {$testName} - " . $e->getMessage() . "\n";
        } finally {
            $summary['totalAssertions'] += $test->getAssertionCount();
            try { $test->tearDown(); } catch (Exception $e) { }
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total Tests: {$summary['totalTests']}\n";
echo "Passed: {$summary['passedTests']}\n";
echo "Failed: {$summary['failedTests']}\n";
echo "Assertions: {$summary['totalAssertions']}\n";
echo "Done.\n";

?>
