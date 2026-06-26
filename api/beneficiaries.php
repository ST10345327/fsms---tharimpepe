<?php
function handleBeneficiaries($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Beneficiary($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $data = $model->getBeneficiaryById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'Beneficiary not found', null, 404);
                }
                apiJsonResponse(true, 'Beneficiary retrieved', $data);
            } else {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $status = $_GET['status'] ?? null;
                $search = $_GET['search'] ?? null;

                if ($search) {
                    $data = $model->searchBeneficiaries($search);
                } else {
                    $data = $model->getAllBeneficiaries($limit, $offset, $status);
                }
                $total = $model->getTotalCount();
                apiJsonResponse(true, 'Beneficiaries retrieved', [
                    'beneficiaries' => $data,
                    'total' => (int)$total
                ]);
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['first_name', 'last_name', 'registration_date']);

            $id = $model->createBeneficiary(
                $input['first_name'],
                $input['last_name'],
                $input['registration_date'],
                $input['age'] ?? null,
                $input['gender'] ?? null,
                $input['phone'] ?? null,
                $input['email'] ?? null,
                $input['address'] ?? null,
                $input['notes'] ?? null
            );
            if ($id) {
                $data = $model->getBeneficiaryById((int)$id);
                apiJsonResponse(true, 'Beneficiary created successfully', $data, 201);
            }
            apiJsonResponse(false, 'Failed to create beneficiary', null, 500);
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'Beneficiary ID is required', null, 400);
            }
            $input = getJsonInput();
            validateRequired($input, ['first_name', 'last_name', 'registration_date', 'status']);

            $result = $model->updateBeneficiary(
                (int)$id,
                $input['first_name'],
                $input['last_name'],
                $input['age'] ?? null,
                $input['gender'] ?? null,
                $input['phone'] ?? null,
                $input['email'] ?? null,
                $input['address'] ?? null,
                $input['registration_date'],
                $input['status'],
                $input['notes'] ?? null
            );
            if ($result) {
                $data = $model->getBeneficiaryById((int)$id);
                apiJsonResponse(true, 'Beneficiary updated successfully', $data);
            }
            apiJsonResponse(false, 'Failed to update beneficiary', null, 500);
            break;

        case 'DELETE':
            if (!$id) {
                apiJsonResponse(false, 'Beneficiary ID is required', null, 400);
            }
            $result = $model->deleteBeneficiary((int)$id);
            if ($result) {
                apiJsonResponse(true, 'Beneficiary deleted successfully');
            }
            apiJsonResponse(false, 'Failed to delete beneficiary', null, 500);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
