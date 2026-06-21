<?php
/**
 * Dashboard Inventory Summary API Endpoint
 * Returns top stock items for dashboard widget
 *
 * Endpoint: GET /api/dashboard/inventory-summary.php
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $stmt = $db->query("
        SELECT ItemName, Quantity, Unit
        FROM foodstock
        ORDER BY Quantity ASC
        LIMIT 5
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($items as $item) {
        // Mock a StockLevel percentage for the UI progress bar
        // In a real system, this would be compared against a 'target' or 'max' quantity.
        $qty = (int)$item['Quantity'];
        $stockLevel = min(100, max(0, ($qty / 100) * 100)); // Assuming 100 is "full" for demo

        $data[] = [
            'ItemName' => $item['ItemName'],
            'Quantity' => $qty,
            'Unit' => $item['Unit'],
            'StockLevel' => $stockLevel
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Inventory summary error: " . $e->getMessage(), 'ERROR');
}
