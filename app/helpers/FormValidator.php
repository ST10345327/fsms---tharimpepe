<?php
/**
 * Module: Form Input Validation Utility
 * Purpose: Centralized validation functions to reduce code duplication
 * Reference: OWASP Input Validation Best Practices (2025)
 * Author: FSMS Development Agent
 * 
 * Features:
 * - Email validation
 * - Username validation
 * - Password strength validation
 * - String length validation
 * - Phone number validation
 * - Date validation
 * - HTML form sanitization
 * - Custom validation rules
 */

class FormValidator
{
    // Validation error messages
    private static $errors = [];
    
    /**
     * HZ-VAL-001
     * Purpose: Reset validation errors
     */
    public static function reset()
    {
        self::$errors = [];
    }
    
    /**
     * HZ-VAL-002
     * Purpose: Get all validation errors
     * 
     * @return array Array of error messages
     */
    public static function getErrors()
    {
        return self::$errors;
    }
    
    /**
     * HZ-VAL-003
     * Purpose: Check if validation has errors
     * 
     * @return bool True if there are errors
     */
    public static function hasErrors()
    {
        return count(self::$errors) > 0;
    }
    
    /**
     * HZ-VAL-004
     * Purpose: Validate email format
     * 
     * @param string $email Email to validate
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateEmail($email, $fieldName = 'Email')
    {
        $email = trim($email ?? '');
        
        if (empty($email)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "{$fieldName} must be a valid email address";
            return false;
        }
        
        if (strlen($email) > 100) {
            self::$errors[] = "{$fieldName} must not exceed 100 characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-005
     * Purpose: Validate username format and length
     * 
     * @param string $username Username to validate
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateUsername($username, $fieldName = 'Username')
    {
        $username = trim($username ?? '');
        
        if (empty($username)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        if (strlen($username) < 3) {
            self::$errors[] = "{$fieldName} must be at least 3 characters";
            return false;
        }
        
        if (strlen($username) > 50) {
            self::$errors[] = "{$fieldName} must not exceed 50 characters";
            return false;
        }
        
        // Allow only alphanumeric and underscore
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            self::$errors[] = "{$fieldName} can only contain letters, numbers, and underscores";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-006
     * Purpose: Validate password strength
     * 
     * @param string $password Password to validate
     * @param string $fieldName Display name for error messages
     * @param int $minLength Minimum password length (default: 6)
     * @return bool True if valid
     */
    public static function validatePassword($password, $fieldName = 'Password', $minLength = 6)
    {
        $password = $_POST[$password] ?? '';
        
        if (empty($password)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        if (strlen($password) < $minLength) {
            self::$errors[] = "{$fieldName} must be at least {$minLength} characters";
            return false;
        }
        
        if (strlen($password) > 255) {
            self::$errors[] = "{$fieldName} must not exceed 255 characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-007
     * Purpose: Validate password confirmation (must match)
     * 
     * @param string $password Password field
     * @param string $passwordConfirm Confirmation field
     * @return bool True if passwords match
     */
    public static function validatePasswordMatch($password, $passwordConfirm)
    {
        $pwd = $_POST[$password] ?? '';
        $pwd_confirm = $_POST[$passwordConfirm] ?? '';
        
        if ($pwd !== $pwd_confirm) {
            self::$errors[] = "Passwords do not match";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-008
     * Purpose: Validate phone number format
     * 
     * @param string $phone Phone number to validate
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validatePhone($phone, $fieldName = 'Phone')
    {
        $phone = trim($phone ?? '');
        
        if (empty($phone)) {
            // Phone is optional, return true
            return true;
        }
        
        if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $phone)) {
            self::$errors[] = "{$fieldName} contains invalid characters";
            return false;
        }
        
        if (strlen($phone) > 20) {
            self::$errors[] = "{$fieldName} must not exceed 20 characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-009
     * Purpose: Validate string length
     * 
     * @param string $value Value to check
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateLength($value, $minLength, $maxLength, $fieldName = 'Field')
    {
        $value = trim($value ?? '');
        $length = strlen($value);
        
        if ($length < $minLength) {
            self::$errors[] = "{$fieldName} must be at least {$minLength} characters";
            return false;
        }
        
        if ($length > $maxLength) {
            self::$errors[] = "{$fieldName} must not exceed {$maxLength} characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-010
     * Purpose: Validate date format (YYYY-MM-DD)
     * 
     * @param string $date Date to validate
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateDate($date, $fieldName = 'Date')
    {
        $date = trim($date ?? '');
        
        if (empty($date)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        // Check format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            self::$errors[] = "{$fieldName} must be in YYYY-MM-DD format";
            return false;
        }
        
        // Validate actual date
        $parts = explode('-', $date);
        if (!checkdate($parts[1], $parts[2], $parts[0])) {
            self::$errors[] = "{$fieldName} is not a valid date";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-011
     * Purpose: Validate integer value
     * 
     * @param string $value Value to validate
     * @param int $min Minimum value (optional)
     * @param int $max Maximum value (optional)
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateInteger($value, $min = null, $max = null, $fieldName = 'Field')
    {
        $value = trim($value ?? '');
        
        if (empty($value)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            self::$errors[] = "{$fieldName} must be a whole number";
            return false;
        }
        
        $value = (int)$value;
        
        if ($min !== null && $value < $min) {
            self::$errors[] = "{$fieldName} must be at least {$min}";
            return false;
        }
        
        if ($max !== null && $value > $max) {
            self::$errors[] = "{$fieldName} must not exceed {$max}";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-012
     * Purpose: Validate decimal/float value
     * 
     * @param string $value Value to validate
     * @param float $min Minimum value (optional)
     * @param float $max Maximum value (optional)
     * @param string $fieldName Display name for error messages
     * @return bool True if valid
     */
    public static function validateDecimal($value, $min = null, $max = null, $fieldName = 'Field')
    {
        $value = trim($value ?? '');
        
        if (empty($value)) {
            self::$errors[] = "{$fieldName} is required";
            return false;
        }
        
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            self::$errors[] = "{$fieldName} must be a valid number";
            return false;
        }
        
        $value = (float)$value;
        
        if ($min !== null && $value < $min) {
            self::$errors[] = "{$fieldName} must be at least {$min}";
            return false;
        }
        
        if ($max !== null && $value > $max) {
            self::$errors[] = "{$fieldName} must not exceed {$max}";
            return false;
        }
        
        return true;
    }
    
    /**
     * HZ-VAL-013
     * Purpose: Sanitize string input (trim, strip tags, encode)
     * 
     * @param string $value Value to sanitize
     * @return string Sanitized value
     */
    public static function sanitizeString($value)
    {
        // Trim whitespace
        $value = trim($value ?? '');
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Encode HTML entities (for safe output)
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * HZ-VAL-014
     * Purpose: Sanitize email input
     * 
     * @param string $value Email to sanitize
     * @return string Sanitized email
     */
    public static function sanitizeEmail($value)
    {
        return filter_var(trim($value ?? ''), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * HZ-VAL-015
     * Purpose: Sanitize URL input
     * 
     * @param string $value URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitizeUrl($value)
    {
        return filter_var(trim($value ?? ''), FILTER_SANITIZE_URL);
    }
    
    /**
     * HZ-VAL-016
     * Purpose: Get required POST parameter with validation
     * 
     * @param string $param Parameter name
     * @param string $fieldName Display name
     * @return string Parameter value or empty string
     */
    public static function getRequired($param, $fieldName = null)
    {
        $value = $_POST[$param] ?? '';
        $fieldName = $fieldName ?? ucfirst(str_replace('_', ' ', $param));
        
        if (empty(trim($value))) {
            self::$errors[] = "{$fieldName} is required";
        }
        
        return trim($value);
    }
    
    /**
     * HZ-VAL-017
     * Purpose: Get optional POST parameter with sanitization
     * 
     * @param string $param Parameter name
     * @param string $default Default value if not provided
     * @return string Parameter value or default
     */
    public static function getOptional($param, $default = '')
    {
        return trim($_POST[$param] ?? $default);
    }
}
?>
