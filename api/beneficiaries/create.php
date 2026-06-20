<?php
/**
 * Beneficiary Create API Endpoint
 * Creates a new beneficiary record
 * 
 * Endpoint: POST /api/beneficiaries/create.php
 * Input: { "first_name": "...", "last_name": "...", "age": N, "gender": "...", "phone": "...", "address": "...", "notes": "..." }
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
    if (!$input || empty($input['first_name']) || empty($input['last_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
        exit();
    }

    $firstName = trim($input['first_name']);
    $lastName = trim($input['last_name']);
    $age = isset($input['age']) ? (int)$input['age'] : null;
    $gender = in_array($input['gender'] ?? '', ['Male', 'Female', 'Other']) ? $input['gender'] : null;
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $address = trim($input['address'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $regDate = $input['registration_date'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $regDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO Beneficiaries (FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes, CreatedBy, CreatedAt) 
         VALUES (:first, :last, :age, :gender, :phone, :email, :address, :regdate, 'active', :notes, :createdby, NOW())"
    );
    $stmt->execute([
        ':first' => $firstName,
        ':last' => $lastName,
        ':age' => $age,
        ':gender' => $gender,
        ':phone' => $phone,
        ':email' => $email,
        ':address' => $address,
        ':regdate' => $regDate,
        ':notes' => $notes,
        ':createdby' => $user['user_id']
    ]);

    $beneficiaryId = (int)$db->lastInsertId();

    logMessage("Beneficiary created: {$firstName} {$lastName} (ID #{$beneficiaryId}) by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Beneficiary created successfully',
        'data' => ['beneficiary_id' => $beneficiaryId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiaries create error: " . $e->getMessage(), 'ERROR');
}