<?php
/**
 * Module: Global Error & Exception Handler
 * Purpose: Centralized error and exception handling for the entire application
 * Reference: PHP Exception Handling Best Practices (PHP Manual, 2025)
 * Author: FSMS Development Agent
 * 
 * Features:
 * - Converts PHP errors to exceptions
 * - Catches uncaught exceptions
 * - Logs errors for debugging
 * - Provides user-friendly error messages
 * - Never exposes sensitive information
 */

class ErrorHandler
{
    /**
     * HZ-ERROR-001
     * Purpose: Initialize global error handling
     * Flow: Register error handler and exception handler
     */
    public static function initialize()
    {
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Handle shutdown to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * HZ-ERROR-002
     * Purpose: Convert PHP errors to ErrorException for consistent handling
     * 
     * @param int $severity Error severity level
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number of error
     * @throws ErrorException
     */
    public static function handleError($severity, $message, $file, $line)
    {
        // Log the error
        self::logError($severity, $message, $file, $line);
        
        // Convert to ErrorException
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    
    /**
     * HZ-ERROR-003
     * Purpose: Handle uncaught exceptions
     * Provides appropriate response based on context (web/API)
     * 
     * @param Throwable $exception The exception to handle
     */
    public static function handleException($exception)
    {
        // Log the exception
        self::logException($exception);

        if (self::isCli()) {
            self::sendCliError($exception);
            exit(1);
        }
        
        // Determine if this is an API request
        $isJson = self::isJsonRequest();
        
        // Generate error response
        if ($isJson) {
            self::sendJsonError($exception);
        } else {
            self::sendHtmlError($exception);
        }
        
        // Exit to prevent further execution
        exit();
    }
    
    /**
     * HZ-ERROR-004
     * Purpose: Catch fatal errors at script shutdown
     * Ensures fatal errors don't result in blank pages
     */
    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log fatal error
            self::logError($error['type'], $error['message'], $error['file'], $error['line']);

            if (self::isCli()) {
                fwrite(STDERR, "Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}" . PHP_EOL);
                return;
            }
            
            // Display appropriate error page
            if (self::isJsonRequest()) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'error_code' => 'FATAL_ERROR'
                ]);
            } else {
                http_response_code(500);
                self::displayErrorPage('Fatal Error', 'An unexpected error occurred. Please try again later.');
            }
        }
    }
    
    /**
     * HZ-ERROR-005
     * Purpose: Log errors to system error log
     * 
     * @param int $severity Error severity
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number of error
     */
    private static function logError($severity, $message, $file, $line)
    {
        $severity_text = self::getSeverityText($severity);
        $log_message = "[{$severity_text}] {$message} in {$file}:{$line}";
        
        error_log($log_message);
    }
    
    /**
     * HZ-ERROR-006
     * Purpose: Log exceptions to error log
     * 
     * @param Throwable $exception The exception to log
     */
    private static function logException($exception)
    {
        $message = sprintf(
            "[%s] %s in %s:%d\nStack Trace:\n%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        error_log($message);
    }
    
    /**
     * HZ-ERROR-007
     * Purpose: Detect if request expects JSON response (API request)
     * 
     * @return bool True if JSON response expected
     */
    private static function isJsonRequest()
    {
        // Check Accept header
        if (!empty($_SERVER['HTTP_ACCEPT'])) {
            return strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        }
        
        // Check if API endpoint
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($request_uri, '/api/') !== false;
    }
    
    /**
     * HZ-ERROR-008
     * Purpose: Send JSON error response for API requests
     * 
     * @param Throwable $exception The exception
     */
    private static function sendJsonError($exception)
    {
        http_response_code(500);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => 'An error occurred while processing your request.',
            'error_code' => 'INTERNAL_ERROR'
        ];
        
        // Include error details in development mode
        if (self::isDevelopmentMode()) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }
        
        echo json_encode($response);
    }
    
    /**
     * HZ-ERROR-009
     * Purpose: Send HTML error page for web requests
     * 
     * @param Throwable $exception The exception
     */
    private static function sendHtmlError($exception)
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        
        $title = 'Application Error';
        $message = 'An unexpected error occurred. Our team has been notified. Please try again later.';
        
        if (self::isDevelopmentMode()) {
            $title = get_class($exception);
            $message = $exception->getMessage();
        }
        
        self::displayErrorPage($title, $message);
    }

    /**
     * Render concise CLI-friendly exceptions without sending web headers.
     */
    private static function sendCliError($exception)
    {
        $message = sprintf(
            "%s: %s in %s:%d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        fwrite(STDERR, $message . PHP_EOL);

        if (self::isDevelopmentMode()) {
            fwrite(STDERR, $exception->getTraceAsString() . PHP_EOL);
        }
    }
    
    /**
     * HZ-ERROR-010
     * Purpose: Display error page HTML
     * 
     * @param string $title Error title
     * @param string $message Error message
     */
    private static function displayErrorPage($title, $message)
    {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            text-align: center;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .error-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">⚠️</div>
            <div class="error-title"><?php echo htmlspecialchars($title); ?></div>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">Go Home</a>
                <button onclick="history.back()" class="btn btn-secondary">Go Back</button>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
    }
    
    /**
     * HZ-ERROR-011
     * Purpose: Convert error severity code to text
     * 
     * @param int $severity Error severity code
     * @return string Severity text
     */
    private static function getSeverityText($severity)
    {
        $levels = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        ];
        
        return $levels[$severity] ?? 'UNKNOWN';
    }
    
    /**
     * HZ-ERROR-012
     * Purpose: Check if application is in development mode
     * 
     * @return bool True if development mode
     */
    private static function isDevelopmentMode()
    {
        return defined('DEBUG_MODE') && DEBUG_MODE === true;
    }

    private static function isCli()
    {
        return PHP_SAPI === 'cli';
    }
}
?>
