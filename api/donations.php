<?php
function handleDonations($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Donation($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $data = $model->getDonationById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'Donation not found', null, 404);
                }
                apiJsonResponse(true, 'Donation retrieved', $data);
            } else {
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $type = $_GET['type'] ?? null;
                $search = $_GET['search'] ?? null;

                if ($search) {
                    $data = $model->searchDonations($search);
                    apiJsonResponse(true, 'Donation search results', $data);
                } elseif ($type) {
                    $data = $model->getDonationsByType($type);
                    apiJsonResponse(true, 'Donations by type retrieved', $data);
                } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $data = $model->getDonationsByDateRange($_GET['start_date'], $_GET['end_date']);
                    apiJsonResponse(true, 'Donations by date range retrieved', $data);
                } else {
                    $result = $model->getAllDonations($page, $limit);
                    $summary = $model->getDonationSummary();
                    apiJsonResponse(true, 'Donations retrieved', [
                        'donations' => $result['data'],
                        'pagination' => $result['pagination'],
                        'summary' => $summary
                    ]);
                }
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['donor_name', 'donation_type', 'donation_date']);

            $result = $model->createDonation([
                'DonorName' => $input['donor_name'],
                'DonorEmail' => $input['donor_email'] ?? '',
                'DonationType' => $input['donation_type'],
                'Amount' => $input['amount'] ?? 0,
                'Description' => $input['description'] ?? '',
                'DonationDate' => $input['donation_date']
            ]);
            if ($result['success']) {
                $data = $model->getDonationById((int)$result['id']);
                apiJsonResponse(true, $result['message'], $data, 201);
            }
            apiJsonResponse(false, $result['message'], null, 500);
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'Donation ID is required', null, 400);
            }
            $input = getJsonInput();
            validateRequired($input, ['donor_name', 'donation_type', 'donation_date']);
            $result = $model->updateDonation((int)$id, [
                'DonorName' => $input['donor_name'],
                'DonorEmail' => $input['donor_email'] ?? '',
                'DonationType' => $input['donation_type'],
                'Amount' => $input['amount'] ?? 0,
                'Description' => $input['description'] ?? '',
                'DonationDate' => $input['donation_date']
            ]);
            if ($result['success']) {
                apiJsonResponse(true, $result['message']);
            }
            apiJsonResponse(false, $result['message'], null, 500);
            break;

        case 'DELETE':
            if (!$id) {
                apiJsonResponse(false, 'Donation ID is required', null, 400);
            }
            $result = $model->deleteDonation((int)$id);
            if ($result['success']) {
                apiJsonResponse(true, $result['message']);
            }
            apiJsonResponse(false, $result['message'], null, 500);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
