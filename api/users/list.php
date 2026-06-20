<?php
/**
 * Users List API Endpoint
 * Returns list of all user accounts
 * 
 * Endpoint: GET /api/users/list.php?role=volunteer&status=active
 * Auth: Bearer token required (admin only)
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

    $role = trim($_GET['role'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $where = [];
    $params = [];

    if ($role !== '' && in_array($role, ['admin', 'volunteer', 'donor', 'staff'])) {
        $where[] = "u.Role = :role";
        $params[':role'] = $role;
    }

    if ($status !== '' && in_array($status, ['active', 'inactive'])) {
        $where[] = "u.Status = :status";
        $params[':status'] = $status;
    }

    if ($search !== '') {
        $where[] = "(u.Username LIKE :search OR u.Email LIKE :search2 OR u.FullName LIKE :search3)";
        $params[':search'] = "%{$search}%";
        $params[':search2'] = "%{$search}%";
        $params[':search3'] = "%{$search}%";
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $db->prepare(
        "SELECT u.UserID, u.Username, u.Email, u.FullName, u.Phone, u.Role, u.Status, u.CreatedAt, u.UpdatedAt
         FROM Users u
         {$whereClause}
         ORDER BY u.FullName ASC, u.Username ASC"
    );
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Users list error: " . $e->getMessage(), 'ERROR');
}