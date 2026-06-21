<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/User.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$input = $input ?: $_POST;

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing credentials', 'received' => $input]);
    exit;
}

try {
    $db = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: '.$e->getMessage()]);
    exit;
}

$userModel = new User($db);
$user = $userModel->authenticate($username, $password);

echo json_encode([
    'success' => (bool) $user,
    'user' => $user,
    'message' => $user ? 'Login OK' : 'Invalid credentials',
]);