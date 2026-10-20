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

    $token = createApiToken($db, $user['UserID']);

    apiJsonResponse(true, 'Login successful', [
        'user' => [
            'id' => (int)$user['UserID'],
            'username' => $user['Username'],
            'email' => $user['Email'],
            'role' => $user['Role'],
        ],
        'token' => $token['token'],
        'token_expires_at' => $token['expires_at']
    ]);
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiJsonResponse(false, 'Method not allowed', null, 405);
    }

    $input = getJsonInput();
    validateRequired($input, ['username', 'email', 'password']);

    $db = getDBConnection();
    $userModel = new User($db);

    try {
        $role = $input['role'] ?? 'volunteer';
        if (!in_array($role, ['donor', 'volunteer'], true)) {
            $role = 'donor';
        }
        $uid = $userModel->register(
            $input['username'],
            $input['email'],
            $input['password'],
            $role,
            $input['full_name'] ?? null,
            $input['phone'] ?? null,
            'pending'
        );
        if ($uid) {
            $volunteerModel = new Volunteer(getDBConnection());
            if ($role === 'volunteer') {
                try {
                    $parts = explode(' ', trim((string)($input['full_name'] ?? '')), 2);
                    $firstName = $parts[0] ?: $input['username'];
                    $lastName = $parts[1] ?? '';
                    $volunteerModel->createVolunteer((int)$uid, $firstName, $lastName, $input['phone'] ?? null, null, 'pending', 'unavailable');
                } catch (Exception $e) {
                    logMessage("Failed to create volunteer profile during API registration: " . $e->getMessage(), 'ERROR');
                }
            }
            $data = $userModel->getUserById((int)$uid);
            unset($data['PasswordHash']);
            apiJsonResponse(true, 'Account created successfully. Your account is pending admin approval. You will be able to log in once an administrator approves your registration.', $data, 201);
        }
        apiJsonResponse(false, 'Failed to create account', null, 500);
    } catch (Exception $e) {
        apiJsonResponse(false, $e->getMessage(), null, 400);
    }
}

function handleLogout() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiJsonResponse(false, 'Method not allowed', null, 405);
    }

    $user = requireAuth();
    $db = getDBConnection();
    revokeApiToken($db, $user['ApiTokenID']);
    apiJsonResponse(true, 'Logout successful');
}
