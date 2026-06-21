<?php
/**
 * System Health Check API
 * Endpoint: GET /api/system/health.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$dbStatus = 'unknown';
try {
    require_once __DIR__ . '/../../app/helpers/bootstrap.php';
    $db = getDBConnection();
    $dbStatus = $db !== null ? 'connected' : 'unavailable';
} catch (Exception $e) {
    $dbStatus = 'error';
}

echo json_encode([
    'status'  => 'ok',
    'app'     => 'FSMS',
    'version' => '1.0',
    'database' => $dbStatus,
    'time'    => date('c')
]);
