<?php
/**
 * Module: API Authentication - Login Endpoint
 * Purpose: Token-based authentication for mobile and API clients
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * 
 * Endpoint: POST /api/auth/login.php
 * Input: { "username": "...", "password": "..." }
 * Output: { "success": true, "token": "...", "user": {...} }
 */

// CORS and JSON headers must come before any output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Bootstrap the application (loads DB, models, helpers)
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';

try {
    // Parse JSON input first so we can still attempt login even if DB is down
    $input = json_decode(file_get_contents('php://input'), true);
    $db = null;
    
    // Attempt database connection (may fail if MySQL is not running)
    try {
        $db = getDBConnection();
    } catch (Exception $dbError) {
        logMessage("Database connection failed, will attempt demo fallback: " . $dbError->getMessage(), 'WARNING');
        $db = null;
    }
    
    if (!$input || empty($input['username']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit();
    }

    $username = trim($input['username']);
    $password = $input['password'];

    // Attempt database authentication
    $userModel = new User($db);
    $user = $userModel->authenticate($username, $password);

    // Fallback to demo users if DB is unavailable
    if (!$user && $db === null) {
        $demoFile = dirname(__DIR__, 2) . '/.demo_users.json';
        if (is_file($demoFile)) {
            $demoUsers = json_decode(file_get_contents($demoFile), true);
            if (!empty($demoUsers[$username]) && 
                password_verify($password, $demoUsers[$username]['password_hash'] ?? '')) {
                $demo = $demoUsers[$username];
                $user = [
                    'UserID' => $demo['user_id'] ?? 1,
                    'Username' => $username,
                    'Email' => $demo['email'] ?? '',
                    'Role' => $demo['role'] ?? 'volunteer'
                ];
            }
        }
    }

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        exit();
    }

    // Generate access token
    $accessToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $accessToken);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Generate refresh token (longer-lived)
    $refreshToken = bin2hex(random_bytes(32));
    $refreshHash = hash('sha256', $refreshToken);
    $refreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Collect device info if available
    $deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Store tokens in database
    if ($db !== null) {
        $stmt = $db->prepare(
            "INSERT INTO AuthTokens (UserID, TokenHash, RefreshTokenHash, ExpiresAt, RefreshExpiresAt, CreatedAt, DeviceInfo, IPAddress) 
             VALUES (:user_id, :token_hash, :refresh_hash, :expires, :refresh_expires, NOW(), :device, :ip)"
        );
        $stmt->execute([
            ':user_id' => $user['UserID'],
            ':token_hash' => $tokenHash,
            ':refresh_hash' => $refreshHash,
            ':expires' => $expiresAt,
            ':refresh_expires' => $refreshExpiresAt,
            ':device' => $deviceInfo,
            ':ip' => $ipAddress
        ]);
    }

    // Log successful login
    logMessage("API login successful for user '{$username}'", 'INFO');

    // Return success response with tokens
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_at' => $expiresAt,
        'user' => [
            'user_id' => (int)$user['UserID'],
            'username' => $user['Username'],
            'email' => $user['Email'] ?? '',
            'role' => $user['Role'] ?? 'volunteer'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal server error occurred'
    ]);
    logMessage("API login error: " . $e->getMessage(), 'ERROR');
}