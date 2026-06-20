<?php
/**
 * Users Change Password API Endpoint
 * Allows a user to change their own password or admin to reset any user's password
 * 
 * Endpoint: POST /api/users/change-password.php
 * Input: { "current_password": "...", "new_password": "..." } or { "user_id": N, "new_password": "..." } (admin)
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $currentUser = $auth->requireAuth();

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['new_password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New password is required']);
        exit();
    }

    $newPassword = $input['new_password'];

    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit();
    }

    // Determine target user
    if (!empty($input['user_id']) && $currentUser['role'] === 'admin') {
        // Admin resetting another user's password
        $targetUserId = (int)$input['user_id'];
    } else {
        // User changing own password
        if (empty($input['current_password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Current password is required']);
            exit();
        }
        $targetUserId = $currentUser['user_id'];

        // Verify current password
        $stmt = $db->prepare("SELECT PasswordHash FROM Users WHERE UserID = :id");
        $stmt->execute([':id' => $targetUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($input['current_password'], $row['PasswordHash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }
    }

    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    $updateStmt = $db->prepare(
        "UPDATE Users SET PasswordHash = :pwd, UpdatedAt = NOW(), UpdatedBy = :updatedby WHERE UserID = :id"
    );
    $updateStmt->execute([
        ':pwd' => $passwordHash,
        ':updatedby' => $currentUser['user_id'],
        ':id' => $targetUserId
    ]);

    // Revoke all existing tokens for this user (force re-login)
    $auth->revokeAllUserTokens($targetUserId);

    logMessage("Password changed for user #{$targetUserId} by user '{$currentUser['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'Password changed successfully. All sessions have been revoked.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Users change-password error: " . $e->getMessage(), 'ERROR');
}