<?php
function handleUsers($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new User($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $data = $model->getUserById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'User not found', null, 404);
                }
                unset($data['PasswordHash']);
                apiJsonResponse(true, 'User retrieved', $data);
            } else {
                $stmt = $db->query("SELECT UserID, Username, Email, FullName, Phone, Role, Status, CreatedAt FROM Users ORDER BY CreatedAt DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                apiJsonResponse(true, 'Users retrieved', $users);
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['username', 'email', 'password']);

            $uid = $model->register(
                $input['username'],
                $input['email'],
                $input['password'],
                $input['role'] ?? 'volunteer',
                $input['full_name'] ?? null,
                $input['phone'] ?? null,
                $input['status'] ?? 'active'
            );
            if ($uid) {
                $data = $model->getUserById((int)$uid);
                unset($data['PasswordHash']);
                apiJsonResponse(true, 'User created successfully', $data, 201);
            }
            apiJsonResponse(false, 'Failed to create user', null, 500);
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'User ID is required', null, 400);
            }
            $input = getJsonInput();

            if (isset($input['password'])) {
                $model->changePassword((int)$id, $input['password']);
                apiJsonResponse(true, 'Password changed successfully');
            } elseif (isset($input['status'])) {
                $model->deactivateUser((int)$id);
                apiJsonResponse(true, 'User status updated');
            }
            apiJsonResponse(false, 'Invalid update request', null, 400);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
