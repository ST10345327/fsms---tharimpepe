<?php
/**
 * Stock Add API Endpoint
 * Adds a new item to food stock
 * 
 * Endpoint: POST /api/stock/add.php
 * Input: { "item_name": "...", "quantity": N, "unit": "...", "expiry_date": "...", "notes": "..." }
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['item_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Item name is required']);
        exit();
    }

    $itemName = trim($input['item_name']);
    $quantity = max(0, (int)($input['quantity'] ?? 0));
    $unit = trim($input['unit'] ?? 'units');
    $expiryDate = trim($input['expiry_date'] ?? '');
    $stockDate = $input['stock_date'] ?? date('Y-m-d');
    $notes = trim($input['notes'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $stockDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    if ($expiryDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiryDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid expiry date format (YYYY-MM-DD)']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO FoodStock (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes, CreatedAt) 
         VALUES (:item, :qty, :unit, :expiry, :sdate, :notes, NOW())"
    );
    $stmt->execute([
        ':item' => $itemName,
        ':qty' => $quantity,
        ':unit' => $unit,
        ':expiry' => $expiryDate ?: null,
        ':sdate' => $stockDate,
        ':notes' => $notes
    ]);

    $stockId = (int)$db->lastInsertId();

    logMessage("Stock item added: {$itemName} ({$quantity} {$unit}) by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Stock item added successfully',
        'data' => ['stock_id' => $stockId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock add error: " . $e->getMessage(), 'ERROR');
}