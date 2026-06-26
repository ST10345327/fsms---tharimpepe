<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Exceptions.php';
require_once __DIR__ . '/../app/helpers/ErrorHandler.php';
require_once __DIR__ . '/../app/helpers/FormValidator.php';

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Beneficiary.php';
require_once __DIR__ . '/../app/models/Attendance.php';
require_once __DIR__ . '/../app/models/Donation.php';
require_once __DIR__ . '/../app/models/FoodStock.php';
require_once __DIR__ . '/../app/models/Volunteer.php';
require_once __DIR__ . '/../app/models/Dashboard.php';
require_once __DIR__ . '/../app/models/Reports.php';
require_once __DIR__ . '/../app/models/MealSession.php';

function getDBConnection() {
    try {
        $database = new Database();
        $conn = $database->connect();
        if (!$conn) {
            throw new Exception("Failed to establish database connection");
        }
        return $conn;
    } catch (PDOException $e) {
        throw new Exception("Database connection error: " . $e->getMessage());
    }
}

function apiJsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        apiJsonResponse(false, 'Invalid JSON input', null, 400);
    }
    return $data ?: [];
}

function requireAuth() {
    $headers = getallheaders();
    $token = null;

    if (isset($headers['Authorization'])) {
        $parts = explode(' ', $headers['Authorization']);
        if (count($parts) === 2 && $parts[0] === 'Bearer') {
            $token = $parts[1];
        }
    }

    if (!$token && isset($_GET['token'])) {
        $token = $_GET['token'];
    }

    if (!$token) {
        apiJsonResponse(false, 'Authentication required', null, 401);
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT UserID, Username, Role, Status FROM Users WHERE MD5(CONCAT(UserID, Username, PasswordHash)) = :token AND Status = 'active' LIMIT 1");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        apiJsonResponse(false, 'Invalid or expired token', null, 401);
    }

    return $user;
}

function generateToken($userId, $username, $passwordHash) {
    return md5($userId . $username . $passwordHash);
}

function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        apiJsonResponse(false, 'Missing required fields: ' . implode(', ', $missing), null, 400);
    }
}
