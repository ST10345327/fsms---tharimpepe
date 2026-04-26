<?php
/**
 * Module: Custom Exception Classes
 * Purpose: Provide application-specific exception types for better error handling
 * Reference: PHP Exception Best Practices (PHP Manual, 2025)
 * Author: FSMS Development Agent
 * 
 * Exception Hierarchy:
 * - FSMSException (base)
 *   - DatabaseException (DB operations)
 *   - ValidationException (Form validation)
 *   - AuthenticationException (Auth failures)
 *   - AuthorizationException (Permission issues)
 *   - ResourceNotFoundException (404 errors)
 *   - ConflictException (Duplicate data)
 */

/**
 * HZ-EXC-001
 * Base exception class for all FSMS exceptions
 */
class FSMSException extends Exception
{
    protected $statusCode = 500;
    protected $userMessage = "An error occurred";
    
    /**
     * Constructor
     * 
     * @param string $message Internal error message (logged)
     * @param int $statusCode HTTP status code
     * @param string $userMessage User-friendly message (displayed)
     * @param Throwable $previous Previous exception
     */
    public function __construct(
        $message = "",
        $statusCode = 500,
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->statusCode = $statusCode;
        if ($userMessage !== null) {
            $this->userMessage = $userMessage;
        }
    }
    
    /**
     * Get HTTP status code
     * 
     * @return int Status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    /**
     * Get user-friendly message
     * 
     * @return string User message
     */
    public function getUserMessage()
    {
        return $this->userMessage;
    }
}

/**
 * HZ-EXC-002
 * Exception for database operation errors
 */
class DatabaseException extends FSMSException
{
    public function __construct(
        $message = "Database operation failed",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            500,
            $userMessage ?? "A database error occurred. Please try again later.",
            $previous
        );
    }
}

/**
 * HZ-EXC-003
 * Exception for form validation errors
 */
class ValidationException extends FSMSException
{
    private $errors = [];
    
    /**
     * Constructor
     * 
     * @param array|string $errors Validation errors
     * @param string $message Internal message
     * @param Throwable $previous Previous exception
     */
    public function __construct($errors = [], $message = "Validation failed", Throwable $previous = null)
    {
        // Store errors array
        if (is_array($errors)) {
            $this->errors = $errors;
        } else {
            $this->errors = [$errors];
        }
        
        parent::__construct(
            $message,
            400,
            "The provided data is invalid. Please check the errors and try again.",
            $previous
        );
    }
    
    /**
     * Get validation errors
     * 
     * @return array Array of error messages
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Get first error
     * 
     * @return string First error message
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? $this->errors[0] : $this->getMessage();
    }
}

/**
 * HZ-EXC-004
 * Exception for authentication failures
 */
class AuthenticationException extends FSMSException
{
    public function __construct(
        $message = "Authentication failed",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            401,
            $userMessage ?? "Invalid username or password",
            $previous
        );
    }
}

/**
 * HZ-EXC-005
 * Exception for authorization failures (permission denied)
 */
class AuthorizationException extends FSMSException
{
    public function __construct(
        $message = "Access denied",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            403,
            $userMessage ?? "You do not have permission to access this resource.",
            $previous
        );
    }
}

/**
 * HZ-EXC-006
 * Exception for resource not found (404)
 */
class ResourceNotFoundException extends FSMSException
{
    public function __construct(
        $resourceType = "Resource",
        $userMessage = null,
        Throwable $previous = null
    ) {
        $message = "{$resourceType} not found";
        parent::__construct(
            $message,
            404,
            $userMessage ?? "The requested {$resourceType} could not be found.",
            $previous
        );
    }
}

/**
 * HZ-EXC-007
 * Exception for conflict errors (duplicate data, state conflicts)
 */
class ConflictException extends FSMSException
{
    public function __construct(
        $message = "A conflict occurred",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            409,
            $userMessage ?? "A conflict occurred while processing your request. This item may already exist.",
            $previous
        );
    }
}

/**
 * HZ-EXC-008
 * Exception for resource already exists (duplicate data)
 */
class DuplicateException extends ConflictException
{
    public function __construct(
        $field = "item",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            "Duplicate {$field}",
            $userMessage ?? "This {$field} already exists. Please try a different one.",
            $previous
        );
    }
}

/**
 * HZ-EXC-009
 * Exception for invalid operation (business logic violation)
 */
class InvalidOperationException extends FSMSException
{
    public function __construct(
        $message = "Invalid operation",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            422,
            $userMessage ?? "This operation cannot be performed at this time.",
            $previous
        );
    }
}

/**
 * HZ-EXC-010
 * Exception for external service errors
 */
class ExternalServiceException extends FSMSException
{
    public function __construct(
        $serviceName = "External Service",
        $userMessage = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            "{$serviceName} error",
            503,
            $userMessage ?? "An external service is temporarily unavailable. Please try again later.",
            $previous
        );
    }
}
?>
