<?php
/**
 * Reports Download API
 * Resolves a report history entry to an export URL.
 *
 * Endpoint: GET /api/reports/download.php?id=beneficiaries:123
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

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $auth->requireAuth();

    $id = trim($_GET['id'] ?? '');
    if ($id === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Report ID is required']);
        exit();
    }

    $validTypes = ['beneficiaries', 'attendance', 'stock', 'volunteers', 'donations', 'financial'];
    $type = $id;
    $logId = null;

    if (strpos($id, ':') !== false) {
        [$type, $logId] = explode(':', $id, 2);
    }

    $type = strtolower(trim($type));
    if (!in_array($type, $validTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
    }

    $format = strtolower($_GET['format'] ?? 'json');
    if (!in_array($format, ['json', 'csv', 'pdf'], true)) {
        $format = 'json';
    }

    $exportPath = '/api/reports/export.php?type=' . urlencode($type) . '&format=' . urlencode($format);

    echo json_encode([
        'success' => true,
        'type' => $type,
        'format' => $format,
        'url' => $exportPath,
        'message' => 'Use the export URL to download this report'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage('Reports download error: ' . $e->getMessage(), 'ERROR');
}
