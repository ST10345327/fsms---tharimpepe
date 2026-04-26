<?php
/**
 * Module: Application Bootstrap & Initialization
 * Purpose: Initialize the application with error handling, configuration, and dependencies
 * Reference: Application Entry Point Best Practices
 * Author: FSMS Development Agent
 * 
 * This file should be included at the very beginning of public/index.php
 * It initializes:
 * - Error handling
 * - Session management
 * - Database connection
 * - Required classes
 */

// Define application constants
define('APP_ROOT', dirname(__DIR__, 2));
define('APP_PATH', APP_ROOT . '/app');
define('CONFIG_PATH', APP_ROOT . '/config');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('MODELS_PATH', APP_PATH . '/models');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', APP_ROOT . '/public');

// Set environment mode (change to false for production)
define('DEBUG_MODE', true);

// Set default timezone
date_default_timezone_set('UTC');

// Set error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Load required exception classes first
require_once HELPERS_PATH . '/Exceptions.php';

// Load error handler
require_once HELPERS_PATH . '/ErrorHandler.php';

// Initialize global error handler
ErrorHandler::initialize();

// Load validation utility
require_once HELPERS_PATH . '/FormValidator.php';

// Load database configuration
require_once CONFIG_PATH . '/database.php';

// Load session handler
require_once HELPERS_PATH . '/SessionHandler.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * HZ-BOOTSTRAP-001
 * Purpose: Get database connection with error handling
 * Returns: PDO connection or throws DatabaseException
 */
function getDBConnection()
{
    try {
        $database = new Database();
        $conn = $database->connect();
        
        if (!$conn) {
            throw new DatabaseException("Failed to establish database connection");
        }
        
        return $conn;
    } catch (PDOException $e) {
        throw new DatabaseException(
            "Database connection error: " . $e->getMessage(),
            null,
            $e
        );
    }
}

/**
 * HZ-BOOTSTRAP-002
 * Purpose: Redirect with session message
 * 
 * @param string $location Redirect URL
 * @param string $message Session message
 * @param string $type Message type (success, error, warning, info)
 */
function redirectWithMessage($location, $message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: {$location}");
    exit();
}

/**
 * HZ-BOOTSTRAP-003
 * Purpose: Get and clear session message
 * 
 * @return array Array with 'message' and 'type' keys, or null if no message
 */
function getSessionMessage()
{
    if (isset($_SESSION['message'])) {
        $message = [
            'message' => $_SESSION['message'],
            'type' => $_SESSION['message_type'] ?? 'info'
        ];
        
        // Clear message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return $message;
    }
    
    return null;
}

/**
 * HZ-BOOTSTRAP-004
 * Purpose: JSON response helper
 * 
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $data Response data
 */
function jsonResponse($success, $message, $data = [])
{
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit();
}

/**
 * HZ-BOOTSTRAP-005
 * Purpose: Format currency for display
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code (default: USD)
 * @return string Formatted currency string
 */
function formatCurrency($amount, $currency = 'USD')
{
    $symbols = [
        'USD' => '$',
        'ZAR' => 'R',
        'EUR' => '€',
        'GBP' => '£'
    ];
    
    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . number_format($amount, 2);
}

/**
 * HZ-BOOTSTRAP-006
 * Purpose: Format date for display
 * 
 * @param string $date Date string (YYYY-MM-DD)
 * @param string $format Output format (default: M d, Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y')
{
    if (empty($date)) {
        return '';
    }
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * HZ-BOOTSTRAP-007
 * Purpose: Format time for display
 * 
 * @param string $time Time string (HH:MM:SS)
 * @param string $format Output format (default: h:i A)
 * @return string Formatted time
 */
function formatTime($time, $format = 'h:i A')
{
    if (empty($time)) {
        return '';
    }
    
    try {
        $timeObj = new DateTime($time);
        return $timeObj->format($format);
    } catch (Exception $e) {
        return $time;
    }
}

/**
 * HZ-BOOTSTRAP-008
 * Purpose: Truncate text to specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Max length
 * @param string $suffix Suffix if truncated (default: ...)
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $suffix = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * HZ-BOOTSTRAP-009
 * Purpose: Log message to system log
 * 
 * @param string $message Message to log
 * @param string $level Log level (INFO, WARNING, ERROR)
 */
function logMessage($message, $level = 'INFO')
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}";
    error_log($logMessage);
}

/**
 * HZ-BOOTSTRAP-010
 * Purpose: Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * HZ-BOOTSTRAP-011
 * Purpose: Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * HZ-BOOTSTRAP-012
 * Purpose: Get CSRF token HTML input field
 * 
 * @return string HTML input field
 */
function csrfTokenInput()
{
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>
