<?php
/**
 * Stock Alerts API
 * Returns low stock and expiry alerts
 * 
 * Endpoint: GET /api/stock/alerts.php
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "low_stock": [...],
 *     "expiry_7_days": [...],
 *     "expiry_14_days": [...],
 *     "expiry_30_days": [...]
 *   }
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

    $result = [
        'low_stock' => [],
        'expiry_7_days' => [],
        'expiry_14_days' => [],
        'expiry_30_days' => [],
        'summary' => [
            'low_stock_count' => 0,
            'expiring_count' => 0,
            'critical_count' => 0
        ]
    ];

    if ($db !== null) {
        // Low stock items
        try {
            $stmt = $db->query("
                SELECT StockID, ItemName, Quantity, Unit, MinStockLevel,
                       CASE 
                           WHEN Quantity = 0 THEN 'critical'
                           WHEN Quantity <= MinStockLevel * 0.5 THEN 'critical'
                           ELSE 'warning'
                       END as severity
                FROM FoodStock
                WHERE Quantity <= MinStockLevel
                ORDER BY 
                    CASE WHEN Quantity = 0 THEN 0 WHEN Quantity <= MinStockLevel * 0.5 THEN 1 ELSE 2 END,
                    Quantity ASC
                LIMIT 20
            ");
            $result['low_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result['summary']['low_stock_count'] = count($result['low_stock']);
            $result['summary']['critical_count'] += count(array_filter($result['low_stock'], fn($i) => $i['severity'] === 'critical'));
        } catch (Exception $e) { /* table may not exist */ }

        // Expiry alerts
        try {
            $stmt = $db->query("
                SELECT StockID, ItemName, Quantity, Unit, ExpiryDate,
                       DATEDIFF(ExpiryDate, CURDATE()) as days_left,
                       CASE 
                           WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 7 THEN 'critical'
                           WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 14 THEN 'warning'
                           ELSE 'info'
                       END as severity
                FROM FoodStock
                WHERE ExpiryDate IS NOT NULL
                  AND DATEDIFF(ExpiryDate, CURDATE()) BETWEEN 0 AND 30
                ORDER BY days_left ASC
                LIMIT 30
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $days = (int)$row['days_left'];
                if ($days <= 7) {
                    $result['expiry_7_days'][] = $row;
                } elseif ($days <= 14) {
                    $result['expiry_14_days'][] = $row;
                } else {
                    $result['expiry_30_days'][] = $row;
                }
            }

            $result['summary']['expiring_count'] = 
                count($result['expiry_7_days']) + 
                count($result['expiry_14_days']) + 
                count($result['expiry_30_days']);
            
            $result['summary']['critical_count'] += count($result['expiry_7_days']);
        } catch (Exception $e) { /* table may not exist */ }
    }

    logMessage("Stock alerts fetched by user '{$user['username']}' - {$result['summary']['low_stock_count']} low stock, {$result['summary']['expiring_count']} expiring", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $result,
        'meta' => ['timestamp' => date('c')]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock alerts error: " . $e->getMessage(), 'ERROR');
}