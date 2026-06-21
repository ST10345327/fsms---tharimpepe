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
    // Disable display_errors for API requests to ensure valid JSON responses
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        ini_set('display_errors', '0');
    } else {
        ini_set('display_errors', '1');
    }
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

// CORS headers for cross-origin requests (mobile app, API clients)
// Only set CORS for API requests to avoid conflicts with web app
$isApiRequest = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
if ($isApiRequest) {
    // Let API endpoints handle their own CORS headers
    // to avoid conflicts with bootstrap headers
} else {
    // For web app requests, allow same-origin
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (!empty($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    // Handle preflight OPTIONS request
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

// Load session handler
require_once HELPERS_PATH . '/SessionHandler.php';

// Configure secure session cookies before starting session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,      // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),  // true if HTTPS
        'httponly' => true,                    // Not accessible via JavaScript
        'samesite' => 'Lax'                    // CSRF protection
    ]);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout (30 minutes of inactivity)
if (isset($_SESSION['last_activity'])) {
    $sessionTimeout = 1800; // 30 minutes
    if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
        // Session expired - destroy it
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redirect to login if this was an authenticated session
        $isApiRequest = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                        || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        
        if (!$isApiRequest && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
            $redirectPath = '/views/login.php?timeout=1';
            header("Location: {$redirectPath}");
            exit();
        }
    }
}

// Update last activity time for authenticated sessions
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
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
        $messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        $message = [
            'message' => $_SESSION['message'],
            'type' => $messageType
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
    
    $symbol = isset($symbols[$currency]) ? $symbols[$currency] : $currency;
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
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

/**
 * HZ-BOOTSTRAP-011
 * Purpose: Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * HZ-BOOTSTRAP-012
 * Purpose: Get CSRF token HTML input field
 * 
 * @return string HTML input field
 */
if (!function_exists('csrfTokenInput')) {
    function csrfTokenInput()
    {
        return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
    }
}
