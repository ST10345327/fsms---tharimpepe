<?php
/**
 * Dashboard Donations Summary API Endpoint
 * Returns recent donations for dashboard widget
 *
 * Endpoint: GET /api/dashboard/donations-summary.php
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
        SELECT DonorName, DonationType, Amount, Description, DonationDate
        FROM donations
        ORDER BY CreatedAt DESC
        LIMIT 5
    ");
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($donations as $d) {
        // Try to parse ItemName from description if it's food
        $item = $d['DonationType'] === 'food' ? explode(':', $d['Description'])[0] : ucfirst($d['DonationType']);

        $data[] = [
            'ItemName' => $item,
            'DonorName' => $d['DonorName'],
            'Date' => $d['DonationDate'],
            'Quantity' => $d['Amount'] ?: 1,
            'Unit' => $d['DonationType'] === 'cash' ? 'ZAR' : 'units',
            'Source' => ucfirst($d['DonationType'])
        ];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Donations summary error: " . $e->getMessage(), 'ERROR');
}
