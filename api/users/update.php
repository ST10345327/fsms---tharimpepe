<?php
/**
 * Users Update API Endpoint
 * Updates a user account details
 * 
 * Endpoint: POST /api/users/update.php
 * Input: { "user_id": N, "full_name": "...", "phone": "...", "role": "...", "status": "active|inactive" }
 * Auth: Bearer token required (admin only)
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
    $user = $auth->requireRole(['admin']);

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit();
    }

    $targetId = (int)$input['user_id'];

    $check = $db->prepare("SELECT UserID FROM Users WHERE UserID = :id");
    $check->execute([':id' => $targetId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    $fields = [];
    $params = [':id' => $targetId];

    if (isset($input['full_name'])) {
        $fields[] = "FullName = :fullname";
        $params[':fullname'] = trim($input['full_name']);
    }
    if (isset($input['phone'])) {
        $fields[] = "Phone = :phone";
        $params[':phone'] = trim($input['phone']);
    }
    if (isset($input['email'])) {
        $email = trim($input['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }
        $fields[] = "Email = :email";
        $params[':email'] = $email;
    }
    if (isset($input['role']) && in_array($input['role'], ['admin', 'volunteer', 'donor', 'staff'])) {
        $fields[] = "Role = :role";
        $params[':role'] = $input['role'];
    }
    if (isset($input['status']) && in_array($input['status'], ['active', 'inactive'])) {
        $fields[] = "Status = :status";
        $params[':status'] = $input['status'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit();
    }

    $fields[] = "UpdatedAt = NOW()";
    $fields[] = "UpdatedBy = :updatedby";
    $params[':updatedby'] = $user['user_id'];

    $sql = "UPDATE Users SET " . implode(', ', $fields) . " WHERE UserID = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logMessage("User #{$targetId} updated by user '{$user['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'User updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Users update error: " . $e->getMessage(), 'ERROR');
}