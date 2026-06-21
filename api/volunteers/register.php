<?php
/**
 * Volunteer Register API Endpoint
 * Registers a new volunteer linked to a user account
 * 
 * Endpoint: POST /api/volunteers/register.php
 * Input: { "first_name": "...", "last_name": "...", "phone": "...", "role": "...", "availability": "...", "start_date": "..." }
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
    $user = $auth->requireRole(['admin', 'staff']);

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['first_name']) || empty($input['last_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First and last name required']);
        exit();
    }

    $firstName = trim($input['first_name']);
    $lastName = trim($input['last_name']);
    $phone = trim($input['phone'] ?? '');
    $role = trim($input['role'] ?? 'Volunteer');
    $availability = trim($input['availability'] ?? 'As needed');
    $startDate = $input['start_date'] ?? date('Y-m-d');

    // Create user account for this volunteer
    $username = strtolower($firstName . '.' . $lastName);
    $email = $username . '@fsms.local';
    $tempPassword = bin2hex(random_bytes(8));
    $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

    $db->beginTransaction();

    // Insert user
    $userStmt = $db->prepare(
        "INSERT INTO Users (Username, Email, PasswordHash, FullName, Phone, Role, Status, CreatedAt) 
         VALUES (:username, :email, :pwd, :fullname, :phone, 'volunteer', 'active', NOW())"
    );
    $userStmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':pwd' => $passwordHash,
        ':fullname' => $firstName . ' ' . $lastName,
        ':phone' => $phone
    ]);
    $newUserId = (int)$db->lastInsertId();

    // Insert volunteer profile
    $volStmt = $db->prepare(
        "INSERT INTO Volunteers (UserID, Skills, AvailabilityStatus, Status, CreatedAt) 
         VALUES (:uid, :skills, 'available', 'pending', NOW())"
    );
    $volStmt->execute([
        ':uid' => $newUserId,
        ':skills' => $role
    ]);
    $volunteerId = (int)$db->lastInsertId();

    // Add initial schedule if start date is provided
    if (!empty($startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $schedStmt = $db->prepare(
            "INSERT INTO VolunteerSchedules (VolunteerID, ScheduleDate, Role, Status, CreatedAt) 
             VALUES (:vid, :sdate, :role, 'scheduled', NOW())"
        );
        $schedStmt->execute([
            ':vid' => $volunteerId,
            ':sdate' => $startDate,
            ':role' => $role
        ]);
    }

    $db->commit();

    logMessage("Volunteer registered: {$firstName} {$lastName} (user #{$newUserId}) by '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Volunteer registered successfully',
        'data' => [
            'volunteer_id' => $volunteerId,
            'user_id' => $newUserId,
            'username' => $username
        ]
    ]);

} catch (Exception $e) {
    if ($db !== null && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteers register error: " . $e->getMessage(), 'ERROR');
}