<?php
/**
 * Stock History API Endpoint
 * Returns distribution history for a specific stock item or all items
 * 
 * Endpoint: GET /api/stock/history.php?stock_id=N&days=30
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
    $user = $auth->requireAuth();

    if ($db === null) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    $stockId = (int)($_GET['stock_id'] ?? 0);
    $days = min(365, max(1, (int)($_GET['days'] ?? 90)));

    $where = "fd.DistributionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
    $params = [':days' => $days];

    if ($stockId > 0) {
        $where .= " AND fd.FoodStockID = :sid";
        $params[':sid'] = $stockId;
    }

    $stmt = $db->prepare(
        "SELECT fd.DistributionID, fd.QuantityDistributed AS quantity, fd.DistributionDate AS `date`,
                fd.Location, fd.Purpose, fd.Notes,
                fs.ItemName, fs.Unit,
                (SELECT Quantity FROM FoodStock WHERE FoodStockID = fs.FoodStockID) AS current_stock
         FROM FoodDistribution fd
         JOIN FoodStock fs ON fd.FoodStockID = fs.FoodStockID
         WHERE {$where}
         ORDER BY fd.DistributionDate DESC, fd.CreatedAt DESC
         LIMIT 100"
    );
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock history error: " . $e->getMessage(), 'ERROR');
}