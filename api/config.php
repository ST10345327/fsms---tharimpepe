<?php
require_once __DIR__ . '/../app/helpers/Security.php';
requireHttpsTransport(true);

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
    $user = getAuthenticatedApiUser();
    if (!$user) {
        apiJsonResponse(false, 'Authentication required', null, 401);
    }
    return $user;
}

function getAuthenticatedApiUser() {
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $authorization = $value;
                break;
            }
        }
    }

    if (!preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim($authorization), $matches)) {
        return null;
    }
    $tokenHash = hash('sha256', strtolower($matches[1]));

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT u.UserID, u.Username, u.Email, u.Role, u.Status, t.TokenID AS ApiTokenID
                          FROM ApiTokens t
                          INNER JOIN Users u ON u.UserID = t.UserID
                          WHERE t.TokenHash = :token_hash
                            AND t.RevokedAt IS NULL
                            AND t.ExpiresAt > UTC_TIMESTAMP()
                            AND u.Status = 'active'
                          LIMIT 1");
    $stmt->execute([':token_hash' => $tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function requireApiRole($user, $roles) {
    if (!in_array(strtolower($user['Role'] ?? ''), array_map('strtolower', (array)$roles), true)) {
        apiJsonResponse(false, 'You do not have permission to perform this action', null, 403);
    }
}

function createApiToken(PDO $db, $userId) {
    $token = bin2hex(random_bytes(32));
    $expiresAt = time() + (30 * 24 * 60 * 60);
    $expiresAtSql = gmdate('Y-m-d H:i:s', $expiresAt);

    $stmt = $db->prepare('INSERT INTO ApiTokens (UserID, TokenHash, ExpiresAt) VALUES (:user_id, :token_hash, :expires_at)');
    $stmt->execute([
        ':user_id' => (int)$userId,
        ':token_hash' => hash('sha256', $token),
        ':expires_at' => $expiresAtSql,
    ]);

    return [
        'token' => $token,
        'expires_at' => gmdate('c', $expiresAt),
    ];
}

function revokeApiToken(PDO $db, $tokenId) {
    $stmt = $db->prepare('UPDATE ApiTokens SET RevokedAt = UTC_TIMESTAMP() WHERE TokenID = :token_id AND RevokedAt IS NULL');
    $stmt->execute([':token_id' => (int)$tokenId]);
}

/** Apply authorization to every routed API operation before dispatch. */
function authorizeApiOperation($resource, $method, $id = null) {
    $permissions = [
        'logout' => ['POST' => ['admin', 'staff', 'volunteer', 'donor']],
        'beneficiaries' => [
            'GET' => ['admin', 'staff'], 'POST' => ['admin'], 'PUT' => ['admin'], 'DELETE' => ['admin'],
        ],
        'attendance' => [
            'GET' => ['admin', 'staff', 'volunteer'], 'POST' => ['admin', 'staff', 'volunteer'],
            'PUT' => ['admin', 'staff'], 'DELETE' => ['admin'],
        ],
        'stock' => [
            'GET' => ['admin', 'staff', 'volunteer'], 'POST' => ['admin', 'staff'],
            'PUT' => ['admin', 'staff'], 'DELETE' => ['admin'],
        ],
        'donations' => [
            'GET' => ['admin', 'staff', 'volunteer', 'donor'],
            'POST' => ['admin', 'staff', 'volunteer', 'donor'],
            'PUT' => ['admin', 'staff', 'volunteer'], 'DELETE' => ['admin'],
        ],
        'volunteers' => [
            'GET' => ['admin', 'staff'], 'POST' => ['admin', 'staff'],
            'PUT' => ['admin', 'staff'], 'DELETE' => ['admin'],
        ],
        'dashboard' => ['GET' => ['admin', 'staff', 'volunteer']],
        'reports' => ['GET' => ['admin', 'staff']],
        'users' => [
            'GET' => $id ? ['admin', 'staff', 'volunteer', 'donor'] : ['admin'],
            'POST' => ['admin'], 'PUT' => ['admin', 'staff', 'volunteer', 'donor'],
        ],
        'mealsessions' => ['GET' => ['admin', 'staff', 'volunteer'], 'POST' => ['admin', 'staff']],
    ];

    if (!isset($permissions[$resource][$method])) {
        apiJsonResponse(false, 'Method not allowed', null, 405);
    }

    // Donation submissions already support anonymous donors; authenticated donor identity is enforced in the handler.
    if ($resource === 'donations' && $method === 'POST') {
        return null;
    }

    $user = requireAuth();
    if ($resource === 'users' && $id && $method !== 'POST'
        && (int)$id !== (int)$user['UserID'] && strtolower($user['Role']) !== 'admin') {
        apiJsonResponse(false, 'You do not have permission to perform this action', null, 403);
    }
    requireApiRole($user, $permissions[$resource][$method]);
    return $user;
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
