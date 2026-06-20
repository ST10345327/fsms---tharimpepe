<?php
/**
 * Stock List API Endpoint
 * Returns current food stock levels with consumption percentages
 * 
 * Endpoint: GET /api/stock/list.php
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

    // Calculate stock levels with consumption percentage based on distributions
    $stmt = $db->query(
        "SELECT fs.FoodStockID, fs.ItemName, fs.Quantity, fs.Unit, fs.ExpiryDate, fs.StockDate,
                COALESCE(SUM(fd.QuantityDistributed), 0) AS total_distributed,
                CASE 
                    WHEN (fs.Quantity + COALESCE(SUM(fd.QuantityDistributed), 0)) > 0 
                    THEN ROUND(fs.Quantity / (fs.Quantity + COALESCE(SUM(fd.QuantityDistributed), 0)) * 100)
                    ELSE 0 
                END AS stock_level
         FROM FoodStock fs
         LEFT JOIN FoodDistribution fd ON fs.FoodStockID = fd.FoodStockID
         GROUP BY fs.FoodStockID
         ORDER BY stock_level ASC"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock list error: " . $e->getMessage(), 'ERROR');
}