<?php
putenv('FSMS_TEST_SQLITE=1');
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../tests/TestCase.php';
require_once __DIR__ . '/../tests/TestAuthenticationAndValidation.php';

$test = new UserAuthenticationTest();
try {
    echo "SCRIPT: calling setUp()\n";
    $test->setUp();
    echo "SCRIPT: calling testUserRegistration()\n";
    $test->testUserRegistration();
    echo "SCRIPT: testUserRegistration completed\n";
    $test->tearDown();
} catch (Exception $e) {
    echo "SCRIPT: Exception: " . $e->getMessage() . "\n";
}

?>