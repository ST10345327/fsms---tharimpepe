<?php
/**
 * Module: User Authentication Tests
 * Purpose: Unit tests for authentication functions (User model)
 * Reference: Test-Driven Development Best Practices
 * Author: FSMS Development Agent
 */

require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/TestCase.php';

/**
 * HZ-TEST-USER-001
 * Test suite for User authentication
 */
class UserAuthenticationTest extends DatabaseTestCase
{
    private $userModel;
    
    /**
     * Setup for each test
     */
    public function setUp()
    {
        parent::setUp();
        
        // Create user model
        $this->userModel = new User($this->getConnection());
    }
    
    /**
     * HZ-TEST-USER-002
     * Test: User registration creates account
     * Expected: New user can be found in database
     */
    public function testUserRegistration()
    {
        $username = 'testuser_' . time();
        $email = 'test_' . time() . '@example.com';
        $password = 'TestPassword123';
        
        // Register user
        $userId = $this->userModel->register($username, $email, $password, 'volunteer');
        
        // Assert user was created
        $this->assertNotNull($userId, "User registration should return user ID");
        $this->assertTrue(is_numeric($userId), "User ID should be numeric");
    }
    
    /**
     * HZ-TEST-USER-003
     * Test: Duplicate username prevents registration
     * Expected: Registration fails with duplicate username error
     */
    public function testDuplicateUsernameRegistration()
    {
        $username = 'duplicatetest_' . time();
        $email1 = 'test1_' . time() . '@example.com';
        $email2 = 'test2_' . time() . '@example.com';
        $password = 'TestPassword123';
        
        // Register first user
        $userId1 = $this->userModel->register($username, $email1, $password, 'volunteer');
        $this->assertNotNull($userId1, "First registration should succeed");
        
        // Try to register with same username
        try {
            $this->userModel->register($username, $email2, $password, 'volunteer');
            throw new AssertionException("Duplicate username should fail registration");
        } catch (Exception $e) {
            $this->assertEquals("Username already exists", $e->getMessage(), "Duplicate username should raise the expected validation error");
        }
    }
    
    /**
     * HZ-TEST-USER-004
     * Test: Username is found in database
     * Expected: User record retrieved successfully
     */
    public function testFindByUsername()
    {
        $username = 'findtest_' . time();
        $email = 'findtest_' . time() . '@example.com';
        $password = 'TestPassword123';
        
        // Register user
        $this->userModel->register($username, $email, $password, 'volunteer');
        
        // Find user
        $user = $this->userModel->findByUsername($username);
        
        $this->assertNotNull($user, "User should be found by username");
        $this->assertEquals($username, $user['Username'], "Username should match");
        $this->assertEquals($email, $user['Email'], "Email should match");
    }
    
    /**
     * HZ-TEST-USER-005
     * Test: Email is found in database
     * Expected: User record retrieved successfully
     */
    public function testFindByEmail()
    {
        $username = 'emailtest_' . time();
        $email = 'emailtest_' . time() . '@example.com';
        $password = 'TestPassword123';
        
        // Register user
        $this->userModel->register($username, $email, $password, 'volunteer');
        
        // Find user
        $user = $this->userModel->findByEmail($email);
        
        $this->assertNotNull($user, "User should be found by email");
        $this->assertEquals($email, $user['Email'], "Email should match");
    }
    
    /**
     * HZ-TEST-USER-006
     * Test: Password authentication succeeds with correct credentials
     * Expected: authenticate() returns user array
     */
    public function testAuthenticationSuccess()
    {
        $username = 'authtest_' . time();
        $email = 'authtest_' . time() . '@example.com';
        $password = 'TestPassword123';
        
        // Register user
        $this->userModel->register($username, $email, $password, 'volunteer');
        
        // Authenticate
        $user = $this->userModel->authenticate($username, $password);
        
        $this->assertNotNull($user, "Authentication should succeed with correct credentials");
        $this->assertEquals($username, $user['Username'], "Returned user should have correct username");
    }
    
    /**
     * HZ-TEST-USER-007
     * Test: Password authentication fails with incorrect password
     * Expected: authenticate() returns false
     */
    public function testAuthenticationFailure()
    {
        $username = 'authfail_' . time();
        $email = 'authfail_' . time() . '@example.com';
        $password = 'TestPassword123';
        $wrongPassword = 'WrongPassword123';
        
        // Register user
        $this->userModel->register($username, $email, $password, 'volunteer');
        
        // Authenticate with wrong password
        $user = $this->userModel->authenticate($username, $wrongPassword);
        
        $this->assertFalse($user, "Authentication should fail with incorrect password");
    }
}

/**
 * HZ-TEST-VALIDATOR-001
 * Test suite for Form Validation
 */
class FormValidatorTest extends TestCase
{
    /**
     * Setup for each test
     */
    public function setUp()
    {
        // Reset validator errors
        FormValidator::reset();
    }
    
    /**
     * HZ-TEST-VALIDATOR-002
     * Test: Valid email passes validation
     * Expected: validateEmail() returns true
     */
    public function testValidEmailValidation()
    {
        $result = FormValidator::validateEmail('test@example.com');
        
        $this->assertTrue($result, "Valid email should pass validation");
        $this->assertFalse(FormValidator::hasErrors(), "No errors should be present");
    }
    
    /**
     * HZ-TEST-VALIDATOR-003
     * Test: Invalid email fails validation
     * Expected: validateEmail() returns false with error
     */
    public function testInvalidEmailValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateEmail('invalid-email');
        
        $this->assertFalse($result, "Invalid email should fail validation");
        $this->assertTrue(FormValidator::hasErrors(), "Errors should be recorded");
    }
    
    /**
     * HZ-TEST-VALIDATOR-004
     * Test: Empty email fails validation
     * Expected: validateEmail() returns false
     */
    public function testEmptyEmailValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateEmail('');
        
        $this->assertFalse($result, "Empty email should fail validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-005
     * Test: Valid username passes validation
     * Expected: validateUsername() returns true
     */
    public function testValidUsernameValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateUsername('validuser123');
        
        $this->assertTrue($result, "Valid username should pass validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-006
     * Test: Short username fails validation
     * Expected: validateUsername() returns false
     */
    public function testShortUsernameValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateUsername('ab');
        
        $this->assertFalse($result, "Short username should fail validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-007
     * Test: Valid phone passes validation
     * Expected: validatePhone() returns true
     */
    public function testValidPhoneValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validatePhone('555-123-4567');
        
        $this->assertTrue($result, "Valid phone should pass validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-008
     * Test: Valid date passes validation
     * Expected: validateDate() returns true
     */
    public function testValidDateValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateDate('2025-12-25');
        
        $this->assertTrue($result, "Valid date should pass validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-009
     * Test: Invalid date fails validation
     * Expected: validateDate() returns false
     */
    public function testInvalidDateValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateDate('2025-13-45');
        
        $this->assertFalse($result, "Invalid date should fail validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-010
     * Test: Valid integer passes validation
     * Expected: validateInteger() returns true
     */
    public function testValidIntegerValidation()
    {
        FormValidator::reset();
        $result = FormValidator::validateInteger('42');
        
        $this->assertTrue($result, "Valid integer should pass validation");
    }
    
    /**
     * HZ-TEST-VALIDATOR-011
     * Test: String sanitization removes HTML
     * Expected: sanitizeString() removes tags
     */
    public function testStringSanitization()
    {
        $dirty = '<script>alert("xss")</script>Hello';
        $clean = FormValidator::sanitizeString($dirty);
        
        $this->assertFalse(strpos($clean, '<script>') !== false, "HTML tags should be removed");
    }
}

// Run tests only when this file is the direct CLI entrypoint.
if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once __DIR__ . '/TestCase.php';
    
    $runner = new TestRunner();
    $runner->addTest(new UserAuthenticationTest());
    $runner->addTest(new FormValidatorTest());
    $runner->printReport();
}
?>
