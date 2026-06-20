<?php
/**
 * Activity Log List API Endpoint
 * Returns recent activity log entries
 * 
 * Endpoint: GET /api/activity/list.php?days=7&action=login&limit=50
 * Auth: Bearer token required (admin/staff only)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireRole(['admin', 'staff']);

    if ($db === null) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    $days = min(90, max(1, (int)($_GET['days'] ?? 7)));
    $limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));
    $action = trim($_GET['action'] ?? '');

    $where = "al.Timestamp >= DATE_SUB(NOW(), INTERVAL :days DAY)";
    $params = [':days' => $days];

    if ($action !== '') {
        $where .= " AND al.Action LIKE :action";
        $params[':action'] = "%{$action}%";
    }

    $stmt = $db->prepare(
        "SELECT al.ActivityID, al.Action, al.Details, al.AffectedEntityName, al.AffectedEntityID,
                al.IPAddress, al.Timestamp,
                u.Username, u.FullName
         FROM ActivityLog al
         JOIN Users u ON al.UserID = u.UserID
         WHERE {$where}
         ORDER BY al.Timestamp DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Activity list error: " . $e->getMessage(), 'ERROR');
}