<?php
/**
 * Stock Distribute API Endpoint
 * Records a food distribution event and decreases stock
 * 
 * Endpoint: POST /api/stock/distribute.php
 * Input: { "stock_id": N, "quantity": N, "location": "...", "purpose": "...", "notes": "..." }
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
    if (!$input || empty($input['stock_id']) || empty($input['quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Stock ID and quantity are required']);
        exit();
    }

    $stockId = (int)$input['stock_id'];
    $quantity = (int)$input['quantity'];
    $location = trim($input['location'] ?? '');
    $purpose = trim($input['purpose'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $distDate = $input['date'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $distDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than zero']);
        exit();
    }

    $db->beginTransaction();

    // Check current stock level
    $check = $db->prepare("SELECT ItemName, Quantity FROM FoodStock WHERE FoodStockID = :id");
    $check->execute([':id' => $stockId]);
    $stock = $check->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Stock item not found']);
        exit();
    }

    if ($stock['Quantity'] < $quantity) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Insufficient stock. Available: {$stock['Quantity']}, Requested: {$quantity}"
        ]);
        exit();
    }

    // Record distribution
    $distStmt = $db->prepare(
        "INSERT INTO FoodDistribution (FoodStockID, QuantityDistributed, DistributionDate, Location, Purpose, Notes, CreatedAt) 
         VALUES (:sid, :qty, :date, :loc, :purpose, :notes, NOW())"
    );
    $distStmt->execute([
        ':sid' => $stockId,
        ':qty' => $quantity,
        ':date' => $distDate,
        ':loc' => $location,
        ':purpose' => $purpose,
        ':notes' => $notes
    ]);

    // Decrease stock
    $updateStmt = $db->prepare(
        "UPDATE FoodStock SET Quantity = Quantity - :qty, UpdatedAt = NOW() WHERE FoodStockID = :id"
    );
    $updateStmt->execute([':qty' => $quantity, ':id' => $stockId]);

    $db->commit();

    logMessage("Stock distributed: {$quantity} of '{$stock['ItemName']}' at {$location} by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Distribution recorded successfully',
        'data' => ['remaining' => max(0, $stock['Quantity'] - $quantity)]
    ]);

} catch (Exception $e) {
    if ($db !== null && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Stock distribute error: " . $e->getMessage(), 'ERROR');
}