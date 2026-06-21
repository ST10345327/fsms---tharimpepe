<?php
/**
 * Donation Receipt API
 * Generates a receipt for a donation
 * 
 * Endpoint: GET /api/donations/receipt.php?id=123&format=json|pdf
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

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Donation ID is required']);
        exit();
    }

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database not available']);
        exit();
    }

    // Fetch donation (schema: Donations stores donor info directly)
    $stmt = $db->prepare("
        SELECT d.*, u.Username as recorded_by
        FROM Donations d
        LEFT JOIN Users u ON d.UserID = u.UserID
        WHERE d.DonationID = :id
    ");
    $stmt->execute([':id' => $id]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Donation not found']);
        exit();
    }

    $receipt = [
        'receipt_number' => 'RCPT-' . str_pad($donation['DonationID'], 8, '0', STR_PAD_LEFT),
        'donation_id' => (int)$donation['DonationID'],
        'donor_name' => $donation['DonorName'] ?? 'Anonymous',
        'donor_contact' => $donation['DonorEmail'] ?? 'N/A',
        'donation_type' => $donation['DonationType'] ?? 'General',
        'amount' => $donation['Amount'] ?? null,
        'donation_date' => $donation['DonationDate'] ?? date('Y-m-d'),
        'description' => $donation['Description'] ?? '',
        'recorded_by' => $donation['recorded_by'] ?? 'system',
        'generated_at' => date('c'),
        'generated_by' => $user['username'] ?? 'system',
        'organization' => 'Tharimpepe Feeding Scheme',
        'thank_you_message' => 'Thank you for your generous donation. Your support helps us feed those in need.'
    ];

    logMessage("Receipt generated for donation #$id by user '{$user['username']}'", 'INFO');

    if ($format === 'pdf') {
        exportReceiptJSON($receipt);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $receipt]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Receipt generation error: " . $e->getMessage(), 'ERROR');
}

function exportReceiptJSON($receipt) {
    $filename = 'receipt_' . $receipt['receipt_number'] . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    echo json_encode([
        'receipt' => $receipt,
        'format' => 'json',
        'note' => 'For PDF generation, use a client-side PDF library or print this receipt'
    ], JSON_PRETTY_PRINT);
    exit(0);
}