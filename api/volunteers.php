<?php
function handleVolunteers($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Volunteer($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $data = $model->getVolunteerById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'Volunteer not found', null, 404);
                }
                apiJsonResponse(true, 'Volunteer retrieved', $data);
            } else {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $status = $_GET['status'] ?? null;
                $search = $_GET['search'] ?? null;

                if ($search) {
                    $data = $model->searchVolunteers($search);
                    apiJsonResponse(true, 'Volunteer search results', $data);
                } elseif ($status === 'available') {
                    $data = $model->getAvailableVolunteers();
                    apiJsonResponse(true, 'Available volunteers retrieved', $data);
                } else {
                    $data = $model->getAllVolunteers($limit, $offset, $status, $search);
                    $total = $model->getVolunteerCount($status, $search);
                    $countByStatus = $model->getVolunteerCountByStatus();
                    apiJsonResponse(true, 'Volunteers retrieved', [
                        'volunteers' => $data,
                        'total' => (int)$total,
                        'counts_by_status' => $countByStatus
                    ]);
                }
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['user_id', 'first_name', 'last_name', 'phone']);

            $vid = $model->createVolunteer(
                $input['user_id'],
                $input['first_name'],
                $input['last_name'],
                $input['phone'],
                $input['address'] ?? null,
                $input['status'] ?? 'pending',
                $input['availability_status'] ?? 'unavailable'
            );
            if ($vid) {
                $data = $model->getVolunteerById((int)$vid);
                apiJsonResponse(true, 'Volunteer created successfully', $data, 201);
            }
            apiJsonResponse(false, 'Failed to create volunteer', null, 500);
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'Volunteer ID is required', null, 400);
            }
            $input = getJsonInput();

            if (isset($input['availability_status'])) {
                $result = $model->updateAvailabilityStatus((int)$id, $input['availability_status']);
                if ($result) {
                    apiJsonResponse(true, 'Availability status updated');
                }
                apiJsonResponse(false, 'Failed to update status', null, 500);
            } else {
                validateRequired($input, ['first_name', 'last_name', 'phone', 'status']);
                $result = $model->updateVolunteer(
                    (int)$id,
                    $input['first_name'],
                    $input['last_name'],
                    $input['phone'],
                    $input['address'] ?? null,
                    $input['status']
                );
                if ($result) {
                    $data = $model->getVolunteerById((int)$id);
                    apiJsonResponse(true, 'Volunteer updated successfully', $data);
                }
                apiJsonResponse(false, 'Failed to update volunteer', null, 500);
            }
            break;

        case 'DELETE':
            if (!$id) {
                apiJsonResponse(false, 'Volunteer ID is required', null, 400);
            }
            $model->deleteVolunteer((int)$id);
            apiJsonResponse(true, 'Volunteer deactivated successfully');
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
