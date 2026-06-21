<?php
/**
 * Module: API Authentication - Logout Endpoint
 * Purpose: Revoke the current Bearer token
 * 
 * Endpoint: POST /api/auth/logout.php
 * Headers: Authorization: Bearer <token>
 * Output: { "success": true, "message": "Logged out successfully" }
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
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = null;
    try {
        $db = getDBConnection();
    } catch (Exception $dbError) {
        logMessage("Database connection failed in logout: " . $dbError->getMessage(), 'ERROR');
    }

    // Extract Bearer token
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] 
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
        ?? '';

    if (empty($authHeader) || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No valid token provided']);
        exit();
    }

    $token = $matches[1];
    $auth = new AuthMiddleware($db);
    $revoked = $auth->revokeToken($token);

    logMessage("API logout: token revoked=" . ($revoked ? 'yes' : 'no'), 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred']);
    logMessage("API logout error: " . $e->getMessage(), 'ERROR');
}