<?php
/**
 * Audit Logs Stats API
 * Endpoint: GET /api/audit/stats.php
 * Auth: Bearer token required
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

    $stats = AuditLogger::getStats();

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    if (class_exists('AuditLogger')) {
        AuditLogger::log('audit_stats_api_error', 'audit', null, null, [
            'response_status' => 500,
            'is_success' => false,
            'failure_reason' => $e->getMessage(),
            'severity' => 'error'
        ]);
    }
}