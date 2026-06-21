<?php
/**
 * Audit Logs API
 * Endpoint: GET /api/audit/logs.php
 * Auth: Bearer token required
 * Query: severity, action_category, action_type, search, limit, offset
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../app/helpers/AuditLogger.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $auth->requireRole(['admin', 'staff']);

    $filters = [];
    if (isset($_GET['severity'])) {
        $filters['severity'] = $_GET['severity'];
    }
    if (isset($_GET['action_category'])) {
        $filters['action_category'] = $_GET['action_category'];
    }
    if (isset($_GET['action_type'])) {
        $filters['action_type'] = $_GET['action_type'];
    }
    if (isset($_GET['user_id'])) {
        $filters['user_id'] = (int)$_GET['user_id'];
    }
    if (isset($_GET['limit'])) {
        $filters['limit'] = (int)$_GET['limit'];
    }
    if (isset($_GET['offset'])) {
        $filters['offset'] = (int)$_GET['offset'];
    }
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    if ($search !== '') {
        $filters['search'] = $search;
    }

    $logs = AuditLogger::getLogs($filters);

    echo json_encode([
        'success' => true,
        'data' => $logs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    if (class_exists('AuditLogger')) {
        AuditLogger::log('audit_logs_api_error', 'audit', null, null, [
            'response_status' => 500,
            'is_success' => false,
            'failure_reason' => $e->getMessage(),
            'severity' => 'error'
        ]);
    }
}