<?php
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiJsonResponse(false, 'Method not allowed', null, 405);
    }

    $input = getJsonInput();
    validateRequired($input, ['username', 'password']);

    $db = getDBConnection();
    $userModel = new User($db);
    $user = $userModel->authenticate($input['username'], $input['password']);

    if (!$user) {
        apiJsonResponse(false, 'Invalid username or password', null, 401);
    }

    if ($user['Status'] !== 'active') {
        apiJsonResponse(false, 'Account is not active. Please contact an administrator.', null, 403);
    }

    $token = generateToken($user['UserID'], $user['Username'], $user['PasswordHash']);

    apiJsonResponse(true, 'Login successful', [
        'user' => [
            'id' => (int)$user['UserID'],
            'username' => $user['Username'],
            'email' => $user['Email'],
            'role' => $user['Role'],
        ],
        'token' => $token
    ]);
}

function handleLogout() {
    apiJsonResponse(true, 'Logout successful');
}
