<?php
/**
 * Beneficiary Delete API Endpoint
 * Soft-deletes a beneficiary by setting status to inactive
 * 
 * Endpoint: POST /api/beneficiaries/delete.php
 * Input: { "id": N }
 * Auth: Bearer token required (admin only)
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
    $user = $auth->requireRole(['admin', 'staff']);

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid beneficiary ID required']);
        exit();
    }

    $stmt = $db->prepare("UPDATE beneficiaries SET Status = 'inactive', UpdatedAt = NOW() WHERE BeneficiaryID = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Beneficiary not found']);
        exit();
    }

    logMessage("Beneficiary #{$id} deactivated by user '{$user['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'Beneficiary deactivated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiaries delete error: " . $e->getMessage(), 'ERROR');
}