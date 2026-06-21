<?php
/**
 * Module: API Authentication - Token Refresh Endpoint
 * Purpose: Refresh an expired access token using a valid refresh token
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * 
 * Endpoint: POST /api/auth/refresh.php
 * Input: { "refresh_token": "..." }
 * Output: { "success": true, "token": "...", "refresh_token": "...", "expires_at": "..." }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $db = null;
    
    try {
        $db = getDBConnection();
    } catch (Exception $dbError) {
        logMessage("Database connection failed in refresh: " . $dbError->getMessage(), 'ERROR');
    }

    if (!$input || empty($input['refresh_token'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Refresh token is required'
        ]);
        exit();
    }

    $refreshToken = $input['refresh_token'];
    $refreshHash = hash('sha256', $refreshToken);

    if ($db === null) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Database unavailable'
        ]);
        exit();
    }

    // Look up refresh token in database
    $stmt = $db->prepare(
        "SELECT t.*, u.Username, u.Email, u.Role, u.Status 
         FROM authtokens t
         JOIN users u ON t.UserID = u.UserID
         WHERE t.RefreshTokenHash = :refresh_hash 
         AND t.RevokedAt IS NULL 
         AND t.RefreshExpiresAt > NOW() 
         AND u.Status = 'active'
         LIMIT 1"
    );
    $stmt->execute([':refresh_hash' => $refreshHash]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Refresh token is invalid or expired'
        ]);
        exit();
    }

    // Revoke the old token
    $revokeStmt = $db->prepare(
        "UPDATE authtokens SET RevokedAt = NOW() WHERE TokenID = :token_id"
    );
    $revokeStmt->execute([':token_id' => $tokenData['TokenID']]);

    // Generate new access token
    $newAccessToken = bin2hex(random_bytes(32));
    $newTokenHash = hash('sha256', $newAccessToken);
    $newExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Generate new refresh token (token rotation)
    $newRefreshToken = bin2hex(random_bytes(32));
    $newRefreshHash = hash('sha256', $newRefreshToken);
    $newRefreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Store new tokens
    $insertStmt = $db->prepare(
        "INSERT INTO authtokens (UserID, TokenHash, RefreshTokenHash, ExpiresAt, RefreshExpiresAt, CreatedAt, DeviceInfo, IPAddress)
         VALUES (:user_id, :token_hash, :refresh_hash, :expires, :refresh_expires, NOW(), :device, :ip)"
    );
    $insertStmt->execute([
        ':user_id' => $tokenData['UserID'],
        ':token_hash' => $newTokenHash,
        ':refresh_hash' => $newRefreshHash,
        ':expires' => $newExpiresAt,
        ':refresh_expires' => $newRefreshExpiresAt,
        ':device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Token refreshed successfully',
        'token' => $newAccessToken,
        'refresh_token' => $newRefreshToken,
        'expires_at' => $newExpiresAt,
        'user' => [
            'user_id' => (int)$tokenData['UserID'],
            'username' => $tokenData['Username'],
            'email' => $tokenData['Email'],
            'role' => $tokenData['Role']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal server error occurred'
    ]);
    logMessage("Token refresh error: " . $e->getMessage(), 'ERROR');
}