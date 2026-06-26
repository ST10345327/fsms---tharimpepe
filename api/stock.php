<?php
function handleStock($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new FoodStock($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $data = $model->getStockById((int)$id);
                if (!$data) {
                    apiJsonResponse(false, 'Stock item not found', null, 404);
                }
                apiJsonResponse(true, 'Stock item retrieved', $data);
            } else {
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
                $filter = $_GET['filter'] ?? null;

                if ($filter === 'low') {
                    $data = $model->getLowStockItems();
                    apiJsonResponse(true, 'Low stock items retrieved', $data);
                } elseif ($filter === 'expired') {
                    $data = $model->getExpiredStock();
                    apiJsonResponse(true, 'Expired stock items retrieved', $data);
                } elseif (isset($_GET['search'])) {
                    $data = $model->searchStock($_GET['search']);
                    apiJsonResponse(true, 'Stock search results', $data);
                } else {
                    $result = $model->getAllStock($page, $limit);
                    $summary = $model->getStockSummary();
                    apiJsonResponse(true, 'Stock items retrieved', [
                        'items' => $result['data'],
                        'pagination' => $result['pagination'],
                        'summary' => $summary
                    ]);
                }
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['item_name', 'quantity', 'unit']);

            $sid = null;
            if (isset($input['operation'])) {
                $id = $input['id'] ?? null;
                if (!$id) {
                    apiJsonResponse(false, 'Stock item ID is required for quantity operations', null, 400);
                }
                $result = $model->updateQuantity((int)$id, (int)$input['quantity'], $input['operation']);
                if ($result['success']) {
                    apiJsonResponse(true, $result['message'], $result);
                }
                apiJsonResponse(false, $result['message'], null, 500);
            } else {
                $result = $model->createStock([
                    'ItemName' => $input['item_name'],
                    'Quantity' => (int)$input['quantity'],
                    'Unit' => $input['unit'],
                    'ExpiryDate' => $input['expiry_date'] ?? null,
                    'Notes' => $input['notes'] ?? null
                ]);
                if ($result['success']) {
                    $data = $model->getStockById((int)$result['id']);
                    apiJsonResponse(true, $result['message'], $data, 201);
                }
                apiJsonResponse(false, $result['message'], null, 500);
            }
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'Stock item ID is required', null, 400);
            }
            $input = getJsonInput();
            validateRequired($input, ['item_name', 'quantity', 'unit']);
            $result = $model->updateStock((int)$id, [
                'ItemName' => $input['item_name'],
                'Quantity' => (int)$input['quantity'],
                'Unit' => $input['unit'],
                'ExpiryDate' => $input['expiry_date'] ?? null,
                'Notes' => $input['notes'] ?? null
            ]);
            if ($result['success']) {
                $data = $model->getStockById((int)$id);
                apiJsonResponse(true, $result['message'], $data);
            }
            apiJsonResponse(false, $result['message'], null, 500);
            break;

        case 'DELETE':
            if (!$id) {
                apiJsonResponse(false, 'Stock item ID is required', null, 400);
            }
            $result = $model->deleteStock((int)$id);
            if ($result['success']) {
                apiJsonResponse(true, $result['message']);
            }
            apiJsonResponse(false, $result['message'], null, 500);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
