<?php
/**
 * Module: API Authentication - Token Validation Endpoint
 * Purpose: Validate an access token and return user information
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * 
 * Endpoint: GET /api/auth/validate.php
 * Headers: Authorization: Bearer <token>
 * Output: { "success": true, "user": {...} }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';

try {
    // Extract Bearer token from Authorization header
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] 
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
        ?? '';
    
    if (empty($authHeader) || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Missing or invalid authorization header'
        ]);
        exit();
    }

    $token = $matches[1];
    $tokenHash = hash('sha256', $token);

    $db = null;
    try {
        $db = getDBConnection();
    } catch (Exception $e) {
        logMessage("Database connection failed in validate: " . $e->getMessage(), 'ERROR');
    }

    if ($db === null) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Database unavailable'
        ]);
        exit();
    }

    // Look up token in database
    $stmt = $db->prepare(
        "SELECT t.*, u.Username, u.Email, u.Role, u.FullName 
         FROM authtokens t
         JOIN users u ON t.UserID = u.UserID
         WHERE t.TokenHash = :token_hash 
         AND t.RevokedAt IS NULL 
         AND t.ExpiresAt > NOW() 
         AND u.Status = 'active'
         LIMIT 1"
    );
    $stmt->execute([':token_hash' => $tokenHash]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Token is invalid or expired'
        ]);
        exit();
    }

    // Update last used timestamp
    $updateStmt = $db->prepare(
        "UPDATE authtokens SET LastUsedAt = NOW() WHERE TokenID = :token_id"
    );
    $updateStmt->execute([':token_id' => $tokenData['TokenID']]);

    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'user' => [
            'user_id' => (int)$tokenData['UserID'],
            'username' => $tokenData['Username'],
            'email' => $tokenData['Email'],
            'fullname' => $tokenData['FullName'] ?? '',
            'role' => $tokenData['Role'],
            'token_expires_at' => $tokenData['ExpiresAt']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal server error occurred'
    ]);
    logMessage("Token validation error: " . $e->getMessage(), 'ERROR');
}