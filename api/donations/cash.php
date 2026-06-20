<?php
/**
 * Donations Cash API Endpoint
 * Records a cash donation (financial contribution)
 * 
 * Endpoint: POST /api/donations/cash.php
 * Input: { "donor_name": "...", "amount": 100.00, "payment_method": "cash|bank_transfer|card", "transaction_ref": "...", "notes": "..." }
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
    if (!$input || empty($input['donor_name']) || !isset($input['amount'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Donor name and amount are required']);
        exit();
    }

    $donorName = trim($input['donor_name']);
    $amount = (float)$input['amount'];
    $donorEmail = trim($input['donor_email'] ?? '');
    $paymentMethod = trim($input['payment_method'] ?? 'cash');
    $transactionRef = trim($input['transaction_ref'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $donationDate = $input['donation_date'] ?? date('Y-m-d');

    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero']);
        exit();
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $donationDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO Donations (DonorName, DonorEmail, DonationType, Amount, Description, PaymentMethod, TransactionReference, Status, DonationDate, UserID, CreatedAt) 
         VALUES (:donor, :email, 'cash', :amount, :desc, :pmethod, :txnref, 'completed', :ddate, :uid, NOW())"
    );
    $stmt->execute([
        ':donor' => $donorName,
        ':email' => $donorEmail,
        ':amount' => $amount,
        ':desc' => $notes ?: "Cash donation of R{$amount}",
        ':pmethod' => $paymentMethod,
        ':txnref' => $transactionRef ?: null,
        ':ddate' => $donationDate,
        ':uid' => $user['user_id']
    ]);

    $donationId = (int)$db->lastInsertId();

    logMessage("Cash donation recorded: R{$amount} from {$donorName} by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Cash donation recorded successfully',
        'data' => ['donation_id' => $donationId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Donations cash error: " . $e->getMessage(), 'ERROR');
}