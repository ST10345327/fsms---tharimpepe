<?php
/**
 * Module: Testing Framework & Utilities
 * Purpose: Unit and integration testing framework for FSMS
 * Reference: PHP Unit Testing Best Practices (2025)
 * Author: FSMS Development Agent
 * 
 * Testing Features:
 * - Unit test runner
 * - Assertion methods
 * - Setup/teardown support
 * - Test reporting
 * - Code coverage tracking
 */

/**
 * HZ-TEST-001
 * Base test case class
 */
class TestCase
{
    protected $testName;
    protected $assertions = 0;
    protected $failures = [];
    protected $setup_called = false;
    
    /**
     * Constructor
     * 
     * @param string $testName Name of test
     */
    public function __construct($testName = '')
    {
        $this->testName = $testName ?: get_class($this);
    }
    
    /**
     * Setup method (called before each test)
     * Override in subclasses
     */
    public function setUp()
    {
    }
    
    /**
     * Teardown method (called after each test)
     * Override in subclasses
     */
    public function tearDown()
    {
    }
    
    /**
     * HZ-TEST-002
     * Assert that condition is true
     * 
     * @param bool $condition Condition to assert
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertTrue($condition, $message = 'Assertion failed')
    {
        $this->assertions++;
        
        if (!$condition) {
            throw new AssertionException($message);
        }
    }
    
    /**
     * HZ-TEST-003
     * Assert that condition is false
     * 
     * @param bool $condition Condition to assert
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertFalse($condition, $message = 'Assertion failed')
    {
        $this->assertions++;
        
        if ($condition) {
            throw new AssertionException($message);
        }
    }
    
    /**
     * HZ-TEST-004
     * Assert that values are equal
     * 
     * @param mixed $expected Expected value
     * @param mixed $actual Actual value
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertEquals($expected, $actual, $message = '')
    {
        $this->assertions++;
        
        if ($expected != $actual) {
            $msg = $message ?: "Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true);
            throw new AssertionException($msg);
        }
    }
    
    /**
     * HZ-TEST-005
     * Assert that values are identical
     * 
     * @param mixed $expected Expected value
     * @param mixed $actual Actual value
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertSame($expected, $actual, $message = '')
    {
        $this->assertions++;
        
        if ($expected !== $actual) {
            $msg = $message ?: "Expected identical values";
            throw new AssertionException($msg);
        }
    }
    
    /**
     * HZ-TEST-006
     * Assert that value is null
     * 
     * @param mixed $value Value to check
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertNull($value, $message = 'Value should be null')
    {
        $this->assertions++;
        
        if ($value !== null) {
            throw new AssertionException($message);
        }
    }
    
    /**
     * HZ-TEST-007
     * Assert that value is not null
     * 
     * @param mixed $value Value to check
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertNotNull($value, $message = 'Value should not be null')
    {
        $this->assertions++;
        
        if ($value === null) {
            throw new AssertionException($message);
        }
    }
    
    /**
     * HZ-TEST-008
     * Assert that array contains key
     * 
     * @param string $key Key to find
     * @param array $array Array to search
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertArrayHasKey($key, $array, $message = '')
    {
        $this->assertions++;
        
        if (!isset($array[$key])) {
            $msg = $message ?: "Key '{$key}' not found in array";
            throw new AssertionException($msg);
        }
    }
    
    /**
     * HZ-TEST-009
     * Assert that string contains substring
     * 
     * @param string $needle Substring to find
     * @param string $haystack String to search
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertStringContains($needle, $haystack, $message = '')
    {
        $this->assertions++;
        
        if (strpos($haystack, $needle) === false) {
            $msg = $message ?: "String does not contain expected substring";
            throw new AssertionException($msg);
        }
    }
    
    /**
     * HZ-TEST-010
     * Assert that value is instance of class
     * 
     * @param string $class Class name
     * @param mixed $object Object to check
     * @param string $message Failure message
     * @throws AssertionException
     */
    protected function assertInstanceOf($class, $object, $message = '')
    {
        $this->assertions++;
        
        if (!($object instanceof $class)) {
            $msg = $message ?: "Object is not instance of {$class}";
            throw new AssertionException($msg);
        }
    }
    
    /**
     * Get assertion count
     * 
     * @return int Number of assertions
     */
    public function getAssertionCount()
    {
        return $this->assertions;
    }

    /**
     * Reset per-method assertion count so suite totals stay accurate.
     */
    public function resetAssertions()
    {
        $this->assertions = 0;
    }
    
    /**
     * Get failures
     * 
     * @return array Array of failures
     */
    public function getFailures()
    {
        return $this->failures;
    }
    
    /**
     * Add failure
     * 
     * @param string $message Failure message
     */
    public function addFailure($message)
    {
        $this->failures[] = $message;
    }
}

/**
 * HZ-TEST-011
 * Exception for assertion failures
 */
class AssertionException extends Exception
{
}

/**
 * HZ-TEST-012
 * Test runner
 */
class TestRunner
{
    private $tests = [];
    private $results = [];
    
    /**
     * Add test
     * 
     * @param TestCase $test Test case
     */
    public function addTest(TestCase $test)
    {
        $this->tests[] = $test;
    }
    
    /**
     * Run all tests
     * 
     * @return array Results
     */
    public function run()
    {
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        $totalAssertions = 0;
        
        foreach ($this->tests as $test) {
            $reflectionClass = new ReflectionClass($test);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if (strpos($method->getName(), 'test') !== 0) {
                    continue;
                }

                $totalTests++;
                $test->resetAssertions();

                try {
                    // Run setup/teardown around each individual test method.
                    $test->setUp();
                    $method->invoke($test);
                    $passedTests++;

                    $this->results[] = [
                        'test' => get_class($test) . '::' . $method->getName(),
                        'status' => 'PASS',
                        'assertions' => $test->getAssertionCount()
                    ];
                } catch (AssertionException $e) {
                    $failedTests++;

                    $this->results[] = [
                        'test' => get_class($test) . '::' . $method->getName(),
                        'status' => 'FAIL',
                        'error' => $e->getMessage(),
                        'assertions' => $test->getAssertionCount()
                    ];
                } catch (Exception $e) {
                    $failedTests++;

                    $this->results[] = [
                        'test' => get_class($test) . '::' . $method->getName(),
                        'status' => 'ERROR',
                        'error' => $e->getMessage(),
                        'assertions' => $test->getAssertionCount()
                    ];
                } finally {
                    $totalAssertions += $test->getAssertionCount();

                    try {
                        $test->tearDown();
                    } catch (Exception $e) {
                        // Ignore teardown failures to preserve the original test result.
                    }
                }
            }
        }
        
        return [
            'totalTests' => $totalTests,
            'passedTests' => $passedTests,
            'failedTests' => $failedTests,
            'totalAssertions' => $totalAssertions,
            'results' => $this->results
        ];
    }
    
    /**
     * Get results
     * 
     * @return array Results
     */
    public function getResults()
    {
        return $this->results;
    }
    
    /**
     * Print test report
     */
    public function printReport($summary = null)
    {
        if ($summary === null) {
            $summary = $this->run();
        }
        
        echo "\n========================================\n";
        echo "TEST REPORT\n";
        echo "========================================\n";
        echo "Total Tests: " . $summary['totalTests'] . "\n";
        echo "Passed: " . $summary['passedTests'] . "\n";
        echo "Failed: " . $summary['failedTests'] . "\n";
        echo "Assertions: " . $summary['totalAssertions'] . "\n";
        echo "----------------------------------------\n\n";
        
        foreach ($summary['results'] as $result) {
            $status = $result['status'] === 'PASS' ? '✓' : '✗';
            echo "{$status} {$result['test']}\n";
            
            if (isset($result['error'])) {
                echo "  Error: {$result['error']}\n";
            }
            
            if (isset($result['assertions'])) {
                echo "  Assertions: {$result['assertions']}\n";
            }
        }
        
        echo "\n========================================\n";
        $passRate = $summary['totalTests'] > 0 
            ? round(($summary['passedTests'] / $summary['totalTests']) * 100, 2) 
            : 0;
        echo "Pass Rate: {$passRate}%\n";
        echo "========================================\n\n";
    }
}

/**
 * HZ-TEST-013
 * Database test case with transaction rollback
 */
class DatabaseTestCase extends TestCase
{
    protected $db;
    protected $inTransaction = false;
    
    /**
     * Setup database connection
     */
    public function setUp()
    {
        try {
            $this->db = getDBConnection();
            $this->db->beginTransaction();
            $this->inTransaction = true;
        } catch (Exception $e) {
            throw new Exception("Database setup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Rollback transaction
     */
    public function tearDown()
    {
        if ($this->inTransaction && $this->db) {
            try {
                $this->db->rollBack();
            } catch (Exception $e) {
                // Ignore rollback errors
            }
        }
    }
    
    /**
     * Get database connection
     * 
     * @return PDO Database connection
     */
    protected function getConnection()
    {
        return $this->db;
    }
}
?>
