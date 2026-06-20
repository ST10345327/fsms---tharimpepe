<?php
/**
 * Stock Low Stock API Endpoint
 * Returns items with low stock levels (near expiry or below threshold)
 * 
 * Endpoint: GET /api/stock/low-stock.php?threshold=25&near_expiry=7
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

    $threshold = max(1, (int)($_GET['threshold'] ?? 25));
    $nearExpiryDays = max(1, (int)($_GET['near_expiry'] ?? 7));

    $stmt = $db->prepare(
        "SELECT fs.FoodStockID, fs.ItemName, fs.Quantity, fs.Unit, fs.ExpiryDate,
                CASE 
                    WHEN fs.ExpiryDate IS NOT NULL AND fs.ExpiryDate <= DATE_ADD(CURDATE(), INTERVAL :expiry DAY) THEN 'expiring_soon'
                    WHEN fs.Quantity <= :threshold THEN 'low_stock'
                    ELSE 'ok'
                END AS alert_type,
                DATEDIFF(fs.ExpiryDate, CURDATE()) AS days_until_expiry,
                COALESCE(SUM(fd.QuantityDistributed), 0) AS total_distributed
         FROM FoodStock fs
         LEFT JOIN FoodDistribution fd ON fs.FoodStockID = fd.FoodStockID
         WHERE fs.Quantity > 0
           AND (fs.Quantity <= :threshold2 
                OR (fs.ExpiryDate IS NOT NULL AND fs.ExpiryDate <= DATE_ADD(CURDATE(), INTERVAL :expiry2 DAY)))
         GROUP BY fs.FoodStockID
         ORDER BY 
            CASE WHEN fs.ExpiryDate IS NOT NULL THEN 0 ELSE 1 END,
            fs.ExpiryDate ASC,
            fs.Quantity ASC"
    );
    $stmt->execute([
        ':threshold' => $threshold,
        ':threshold2' => $threshold,
        ':expiry' => $nearExpiryDays,
        ':expiry2' => $nearExpiryDays
    ]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'alert_count' => count($data)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock low-stock error: " . $e->getMessage(), 'ERROR');
}