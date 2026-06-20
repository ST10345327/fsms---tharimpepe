<?php
/**
 * Users Create API Endpoint
 * Creates a new user account
 * 
 * Endpoint: POST /api/users/create.php
 * Input: { "username": "...", "email": "...", "password": "...", "full_name": "...", "phone": "...", "role": "volunteer|staff|donor" }
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
    $user = $auth->requireRole(['admin']);

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['username']) || empty($input['password']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username, email, and password are required']);
        exit();
    }

    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    $fullName = trim($input['full_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $role = in_array($input['role'] ?? 'volunteer', ['admin', 'volunteer', 'donor', 'staff']) ? $input['role'] : 'volunteer';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }

    // Check for existing username or email
    $checkStmt = $db->prepare("SELECT UserID FROM Users WHERE Username = :username OR Email = :email LIMIT 1");
    $checkStmt->execute([':username' => $username, ':email' => $email]);
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare(
        "INSERT INTO Users (Username, Email, PasswordHash, FullName, Phone, Role, Status, CreatedAt, CreatedBy) 
         VALUES (:username, :email, :pwd, :fullname, :phone, :role, 'active', NOW(), :createdby)"
    );
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':pwd' => $passwordHash,
        ':fullname' => $fullName,
        ':phone' => $phone,
        ':role' => $role,
        ':createdby' => $user['user_id']
    ]);

    $userId = (int)$db->lastInsertId();

    logMessage("User created: {$username} ({$role}) by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'data' => ['user_id' => $userId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Users create error: " . $e->getMessage(), 'ERROR');
}