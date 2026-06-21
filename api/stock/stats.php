<?php
/**
 * Food Stock Statistics API
 * Endpoint: GET /api/stock/stats.php
 * Auth: Bearer token required
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
    $auth->requireAuth();

    $stats = [
        'total_items' => 0,
        'low_stock' => 0,
        'expiring_soon' => 0,
        'total_value' => 0
    ];

    if ($db !== null) {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE Quantity > 0");
        $stats['total_items'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE Quantity <= 25 AND Quantity > 0");
        $stats['low_stock'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE ExpiryDate IS NOT NULL AND ExpiryDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND ExpiryDate >= CURDATE()");
        $stats['expiring_soon'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    }

    echo json_encode(['success' => true, 'data' => $stats]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock stats error: " . $e->getMessage(), 'ERROR');
}
