# FSMS Testing Framework

## Overview

The FSMS testing framework provides unit and integration testing capabilities for critical functionality.

## Running Tests

### Run All Tests
```bash
php tests/run_all_tests.php
```

### Run Specific Test Suite
```bash
php tests/TestAuthenticationAndValidation.php
```

## Test Structure

```
tests/
├── TestCase.php                       # Base test classes
├── TestAuthenticationAndValidation.php # Auth & validation tests
├── run_all_tests.php                  # Test runner
└── README.md                          # This file
```

## Writing New Tests

### 1. Create Test File
Create a new file in `tests/` directory named `TestFeatureName.php`

### 2. Extend TestCase
```php
class YourFeatureTest extends TestCase
{
    public function testSomething()
    {
        $this->assertTrue(true, "Should be true");
    }
}
```

### 3. Run Tests
```bash
php tests/YourFeatureTest.php
```

## Available Assertions

| Method | Description |
|--------|-------------|
| `assertTrue($condition)` | Assert condition is true |
| `assertFalse($condition)` | Assert condition is false |
| `assertEquals($expected, $actual)` | Assert values are equal |
| `assertSame($expected, $actual)` | Assert values are identical |
| `assertNull($value)` | Assert value is null |
| `assertNotNull($value)` | Assert value is not null |
| `assertArrayHasKey($key, $array)` | Assert array has key |
| `assertStringContains($needle, $haystack)` | Assert string contains substring |
| `assertInstanceOf($class, $object)` | Assert object is instance of class |

## Database Testing

For database tests, extend `DatabaseTestCase` instead of `TestCase`:

```php
class YourDatabaseTest extends DatabaseTestCase
{
    public function testDatabaseOperation()
    {
        // Automatically wrapped in transaction
        // Automatically rolled back after test
        
        $db = $this->getConnection();
        // ... test code ...
    }
}
```

**Important:** Transactions are automatically rolled back after each test, preventing test data pollution.

## Test Coverage

### Phase 1 - Critical Functions
- ✅ User authentication
- ✅ Form validation
- ⏳ Authorization/permissions (Phase 2)
- ⏳ Data operations (Phase 2)
- ⏳ Error handling (Phase 2)

### Phase 2 - Integration Tests
- Beneficiary CRUD operations
- Attendance tracking workflows
- Donation management
- Food stock operations
- Report generation

## Test Naming Convention

Tests follow this naming pattern:
- Test class: `FeatureNameTest`
- Test method: `testSomethingDescriptive`
- Example: `UserAuthenticationTest::testAuthenticationSuccess`

## Reporting

Test runner provides detailed report:
```
========================================
TEST REPORT
========================================
Total Tests: 15
Passed: 14
Failed: 1
Assertions: 45
----------------------------------------

✓ UserAuthenticationTest::testUserRegistration
  Assertions: 2

✗ FormValidatorTest::testInvalidEmail
  Error: Expected validation to fail
  Assertions: 1

========================================
Pass Rate: 93.33%
========================================
```

## Best Practices

1. **One assertion per test** when possible
2. **Descriptive test names** - say what you're testing
3. **Setup/Teardown** - use setUp() and tearDown()
4. **Database tests** - extend DatabaseTestCase
5. **No test interdependencies** - tests should run independently
6. **Clean test data** - tearDown() handles cleanup automatically

## Continuous Integration

To run tests in CI environment:
```bash
php tests/run_all_tests.php > test_results.txt 2>&1
```

Check exit code:
- 0 = All tests passed
- 1 = Some tests failed

## Common Issues

### Test fails with "Database setup failed"
- Ensure database is running
- Check database credentials in `config/database.php`
- Verify user has permissions

### Transaction rollback fails
- Make sure using `DatabaseTestCase` not `TestCase`
- Check database supports transactions

### Tests running sequentially
- Tests intentionally run sequentially for stability
- Use TestRunner for parallel execution in future versions

---

**Need help?** Check the example tests in `TestAuthenticationAndValidation.php`
