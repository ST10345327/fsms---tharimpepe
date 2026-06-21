<?php
/**
 * Beneficiary Update API Endpoint
 * Updates an existing beneficiary record
 * 
 * Endpoint: POST /api/beneficiaries/update.php
 * Input: { "id": N, "first_name": "...", "last_name": "...", ... }
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
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Beneficiary ID is required']);
        exit();
    }

    $id = (int)$input['id'];

    // Verify beneficiary exists
    $check = $db->prepare("SELECT BeneficiaryID FROM beneficiaries WHERE BeneficiaryID = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Beneficiary not found']);
        exit();
    }

    $fields = [];
    $params = [':id' => $id];

    if (!empty($input['first_name'])) {
        $fields[] = "FirstName = :first";
        $params[':first'] = trim($input['first_name']);
    }
    if (!empty($input['last_name'])) {
        $fields[] = "LastName = :last";
        $params[':last'] = trim($input['last_name']);
    }
    if (isset($input['age'])) {
        $fields[] = "Age = :age";
        $params[':age'] = (int)$input['age'];
    }
    if (isset($input['gender']) && in_array($input['gender'], ['Male', 'Female', 'Other'])) {
        $fields[] = "Gender = :gender";
        $params[':gender'] = $input['gender'];
    }
    if (isset($input['phone'])) {
        $fields[] = "Phone = :phone";
        $params[':phone'] = trim($input['phone']);
    }
    if (isset($input['email'])) {
        $fields[] = "Email = :email";
        $params[':email'] = trim($input['email']);
    }
    if (isset($input['address'])) {
        $fields[] = "Address = :address";
        $params[':address'] = trim($input['address']);
    }
    if (isset($input['status']) && in_array($input['status'], ['active', 'inactive', 'suspended'])) {
        $fields[] = "Status = :status";
        $params[':status'] = $input['status'];
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
    $sql = "UPDATE beneficiaries SET " . implode(', ', $fields) . " WHERE BeneficiaryID = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logMessage("Beneficiary #{$id} updated by user '{$user['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'Beneficiary updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiaries update error: " . $e->getMessage(), 'ERROR');
}