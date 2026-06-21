<?php
/**
 * Beneficiary Get API Endpoint
 * Returns a single beneficiary by ID
 * 
 * Endpoint: GET /api/beneficiaries/get.php?id=N
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

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid beneficiary ID required']);
        exit();
    }

    $stmt = $db->prepare(
        "SELECT BeneficiaryID, FirstName, LastName,
                CONCAT(FirstName, ' ', LastName) AS FullName,
                Age, Gender, Phone, Email, Address,
                RegistrationDate, Status, Notes, CreatedAt, UpdatedAt
         FROM beneficiaries
         WHERE BeneficiaryID = :id
         LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    $beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$beneficiary) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Beneficiary not found']);
        exit();
    }

    echo json_encode(['success' => true, 'data' => $beneficiary]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiaries get error: " . $e->getMessage(), 'ERROR');
}