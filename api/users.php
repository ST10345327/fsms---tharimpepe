<?php
function handleUsers($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new User($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                if ((int)$id !== (int)$user['UserID']) {
                    requireApiRole($user, ['admin']);
                }
                $data = $model->getUserById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'User not found', null, 404);
                }
                unset($data['PasswordHash']);
                apiJsonResponse(true, 'User retrieved', $data);
            } else {
                requireApiRole($user, ['admin']);
                $stmt = $db->query("SELECT UserID, Username, Email, FullName, Phone, Role, Status, CreatedAt FROM Users ORDER BY CreatedAt DESC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                apiJsonResponse(true, 'Users retrieved', $users);
            }
            break;

        case 'POST':
            requireApiRole($user, ['admin']);
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

            $isSelf = (int)$id === (int)$user['UserID'];
            if (!$isSelf) {
                requireApiRole($user, ['admin']);
            }

            if (isset($input['password'])) {
                if ($isSelf) {
                    $authUser = $model->getUserAuthById((int)$id);
                    if (!$authUser || empty($input['current_password']) || !password_verify($input['current_password'], $authUser['PasswordHash'])) {
                        apiJsonResponse(false, 'Current password is incorrect', null, 400);
                    }
                    if (($input['confirm_password'] ?? '') !== $input['password']) {
                        apiJsonResponse(false, 'New passwords do not match', null, 400);
                    }
                }
                $model->changePassword((int)$id, $input['password']);
                apiJsonResponse(true, 'Password changed successfully');
            } elseif (isset($input['email']) || isset($input['full_name']) || isset($input['phone']) || isset($input['role']) || isset($input['status'])) {
                $existing = $model->getUserById((int)$id);
                if (!$existing) {
                    apiJsonResponse(false, 'User not found', null, 404);
                }
                $allowedRoles = ['admin', 'staff', 'volunteer', 'donor'];
                $allowedStatuses = ['active', 'pending', 'inactive'];
                $role = $input['role'] ?? ($existing['Role'] ?? null);
                $status = $input['status'] ?? ($existing['Status'] ?? null);
                if (!$isSelf && !in_array($role, $allowedRoles, true)) {
                    apiJsonResponse(false, 'Invalid user role', null, 400);
                }
                if (!$isSelf && !in_array($status, $allowedStatuses, true)) {
                    apiJsonResponse(false, 'Invalid user status', null, 400);
                }
                $model->updateDetails(
                    (int)$id,
                    $input['email'] ?? $existing['Email'],
                    $input['full_name'] ?? $existing['FullName'],
                    $input['phone'] ?? $existing['Phone'],
                    $isSelf ? null : $role,
                    $isSelf ? null : $status
                );
                apiJsonResponse(true, 'User updated successfully', $model->getUserById((int)$id));
            }
            apiJsonResponse(false, 'Invalid update request', null, 400);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
