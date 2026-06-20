<?php
/**
 * Stock Update API Endpoint
 * Updates a stock item's details (quantity, unit, expiry, etc.)
 * 
 * Endpoint: POST /api/stock/update.php
 * Input: { "stock_id": N, "item_name": "...", "quantity": N, "unit": "...", "expiry_date": "...", "notes": "..." }
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
    if (!$input || empty($input['stock_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Stock ID is required']);
        exit();
    }

    $stockId = (int)$input['stock_id'];

    $check = $db->prepare("SELECT FoodStockID FROM FoodStock WHERE FoodStockID = :id");
    $check->execute([':id' => $stockId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Stock item not found']);
        exit();
    }

    $fields = [];
    $params = [':id' => $stockId];

    if (!empty($input['item_name'])) {
        $fields[] = "ItemName = :item";
        $params[':item'] = trim($input['item_name']);
    }
    if (isset($input['quantity'])) {
        $fields[] = "Quantity = :qty";
        $params[':qty'] = max(0, (int)$input['quantity']);
    }
    if (isset($input['unit'])) {
        $fields[] = "Unit = :unit";
        $params[':unit'] = trim($input['unit']);
    }
    if (isset($input['expiry_date'])) {
        $expiry = trim($input['expiry_date']);
        if ($expiry !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid expiry date format']);
            exit();
        }
        $fields[] = "ExpiryDate = :expiry";
        $params[':expiry'] = $expiry ?: null;
    }
    if (isset($input['notes'])) {
        $fields[] = "Notes = :notes";
        $params[':notes'] = trim($input['notes']);
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit();
    }

    $fields[] = "UpdatedAt = NOW()";
    $sql = "UPDATE FoodStock SET " . implode(', ', $fields) . " WHERE FoodStockID = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logMessage("Stock item #{$stockId} updated by user '{$user['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'Stock item updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock update error: " . $e->getMessage(), 'ERROR');
}