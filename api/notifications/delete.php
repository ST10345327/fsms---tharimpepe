<?php
/**
 * Notifications Delete API Endpoint
 *
 * Endpoint: DELETE /api/notifications/delete.php?id=xxx
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();

    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Notification ID required']);
        exit();
    }

    if (strpos($id, 'msg_') === 0) {
        $msgId = str_replace('msg_', '', $id);
        if ($db !== null) {
            // Check if broadcast message (RecipientID IS NULL) — admin only
            $checkStmt = $db->prepare("SELECT RecipientID FROM Messages WHERE MessageID = :id LIMIT 1");
            $checkStmt->execute([':id' => $msgId]);
            $msg = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$msg) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
                exit();
            }

            if ($msg['RecipientID'] === null && !in_array($user['role'], ['admin', 'staff'], true)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Only admins can delete broadcast messages']);
                exit();
            }

            $stmt = $db->prepare("DELETE FROM Messages WHERE MessageID = :id AND (RecipientID = :uid OR (RecipientID IS NULL AND :is_admin = 1))");
            $stmt->execute([
                ':id' => $msgId,
                ':uid' => $user['user_id'],
                ':is_admin' => in_array($user['role'], ['admin', 'staff'], true) ? 1 : 0
            ]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Notification deleted']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Notification delete error: " . $e->getMessage(), 'ERROR');
}
