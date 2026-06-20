<?php
/**
 * Donations Record API Endpoint
 * Records a new donation (food/supplies)
 * 
 * Endpoint: POST /api/donations/record.php
 * Input: { "donor_name": "...", "item_name": "...", "quantity": 50, "unit": "kg" }
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

    $donorName = trim($input['donor_name'] ?? 'Anonymous');
    $itemName = trim($input['item_name']);
    $quantity = max(0, (float)($input['quantity'] ?? 0));
    $unit = trim($input['unit'] ?? 'units');

    $db->beginTransaction();

    // Record the donation
    $stmt = $db->prepare(
        "INSERT INTO Donations (DonorName, DonationType, Description, DonationDate, Status, UserID, CreatedAt) 
         VALUES (:donor, 'food', :desc, CURDATE(), 'completed', :uid, NOW())"
    );
    $stmt->execute([
        ':donor' => $donorName,
        ':desc' => "{$itemName}: {$quantity} {$unit}",
        ':uid' => $user['user_id']
    ]);
    $donationId = $db->lastInsertId();

    // Add to food stock
    $stockStmt = $db->prepare(
        "INSERT INTO FoodStock (ItemName, Quantity, Unit, StockDate, Notes, CreatedAt) 
         VALUES (:item, :qty, :unit, CURDATE(), :notes, NOW())"
    );
    $stockStmt->execute([
        ':item' => $itemName,
        ':qty' => (int)$quantity,
        ':unit' => $unit,
        ':notes' => "Donation from {$donorName}"
    ]);

    $db->commit();

    logMessage("Donation recorded: {$itemName} {$quantity}{$unit} from {$donorName} by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Donation recorded successfully',
        'data' => ['donation_id' => (int)$donationId]
    ]);

} catch (Exception $e) {
    if ($db !== null && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Donations record error: " . $e->getMessage(), 'ERROR');
}