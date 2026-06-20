<?php
/**
 * Donations List API Endpoint
 * Returns recent donation records
 * 
 * Endpoint: GET /api/donations/list.php?limit=20
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

    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

    if ($db === null) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    $stmt = $db->prepare(
        "SELECT d.DonationID, d.DonorName AS donor, d.DonorEmail, 
                d.DonationType AS type, d.Description, 
                d.DonationDate AS `date`, d.Status, d.Amount,
                CASE 
                    WHEN d.DonationType = 'cash' THEN CONCAT('R', FORMAT(d.Amount, 2))
                    ELSE d.Description 
                END AS item_name,
                CASE 
                    WHEN d.DonationType = 'cash' THEN d.Amount
                    ELSE NULL 
                END AS quantity,
                CASE 
                    WHEN d.DonationType = 'cash' THEN 'ZAR'
                    ELSE 'units' 
                END AS unit
         FROM Donations d
         ORDER BY d.DonationDate DESC, d.CreatedAt DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Donations list error: " . $e->getMessage(), 'ERROR');
}