<?php
// Usage: php run_test_class.php ClassName
putenv('FSMS_TEST_SQLITE=1');
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../tests/TestCase.php';

$argvClass = isset($argv[1]) ? $argv[1] : '';
if (!$argvClass) {
    echo "Usage: php run_test_class.php ClassName\n";
    exit(2);
}

// Load all test files so classes are available
$testDir = __DIR__ . '/../tests';
foreach (glob($testDir . '/Test*.php') as $file) {
    if (basename($file) === 'TestCase.php') continue;
    require_once $file;
}

if (!class_exists($argvClass)) {
    echo "Class {$argvClass} not found.\n";
    exit(3);
}

$test = new $argvClass();
$reflection = new ReflectionClass($test);
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

foreach ($methods as $method) {
    if (strpos($method->getName(), 'test') !== 0) continue;

    $testName = get_class($test) . '::' . $method->getName();
    echo "--> RUNNING: {$testName}\n";
    if (function_exists('flush')) { @flush(); }

    try {
        $test->setUp();
        $method->invoke($test);
        echo "    COMPLETED: {$testName}\n";
    } catch (Exception $e) {
        echo "    ERROR: {$testName} - " . $e->getMessage() . "\n";
    } finally {
        try { $test->tearDown(); } catch (Exception $e) { }
    }

    if (function_exists('flush')) { @flush(); }
}

echo "Done.\n";

?>
