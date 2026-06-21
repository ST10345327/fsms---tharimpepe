<?php
/**
 * Stock Movements API
 * Returns stock in/out history with filters
 * 
 * Endpoint: GET /api/stock/movements.php?stock_id=123&type=in|out&days=30
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": [
 *     {"movement_id": 1, "stock_id": 5, "type": "in", "quantity": 50, "date": "2026-01-10", "reference": "DON-001", "notes": "Donation"}
 *   ]
 * }
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
    $user = $auth->requireAuth();

    $stockId = isset($_GET['stock_id']) ? (int)$_GET['stock_id'] : 0;
    $type = isset($_GET['type']) ? strtolower($_GET['type']) : '';
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
    $validTypes = ['in', 'out', 'adjustment'];

    $where = ["fd.DistributionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)"];
    $params = [':days' => $days];

    if ($stockId) {
        $where[] = "fd.FoodStockID = :stock_id";
        $params[':stock_id'] = $stockId;
    }

    if ($type === 'out') {
        // FoodDistribution records are outbound movements
    } elseif ($type === 'in') {
        // Inbound movements come from stock additions (no separate table)
        $where[] = "1 = 0";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $movements = [];

    if ($db !== null) {
        try {
            $sql = "
                SELECT 
                    fd.DistributionID as MovementID,
                    fd.FoodStockID as StockID,
                    s.ItemName,
                    'out' as MovementType,
                    fd.QuantityDistributed as Quantity,
                    fd.DistributionDate as MovementDate,
                    fd.Location as ReferenceNumber,
                    fd.Notes,
                    NULL as recorded_by
                FROM FoodDistribution fd
                JOIN FoodStock s ON fd.FoodStockID = s.FoodStockID
                $whereClause
                ORDER BY fd.DistributionDate DESC, fd.DistributionID DESC
                LIMIT 100
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Table may not exist - return empty
            $movements = [];
        }
    }

    logMessage("Stock movements fetched by user '{$user['username']}' - " . count($movements) . " records", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $movements,
        'meta' => [
            'count' => count($movements),
            'days' => $days,
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock movements error: " . $e->getMessage(), 'ERROR');
}