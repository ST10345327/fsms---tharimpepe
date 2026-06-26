<?php
function handleAttendance($method, $id, $subresource) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Attendance($db);

    switch ($method) {
        case 'GET':
            if ($subresource === 'today') {
                $today = date('Y-m-d');
                $data = $model->getDailyAttendanceSummary($today);
                $stats = $model->getAttendanceStats($today, $today);
                apiJsonResponse(true, 'Today attendance retrieved', [
                    'records' => $data,
                    'stats' => $stats
                ]);
            } elseif ($subresource === 'history') {
                $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
                $data = $model->getAttendanceHistory($days);
                apiJsonResponse(true, 'Attendance history retrieved', $data);
            } elseif ($subresource === 'stats') {
                $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
                $endDate = $_GET['end_date'] ?? date('Y-m-d');
                $stats = $model->getAttendanceStats($startDate, $endDate);
                apiJsonResponse(true, 'Attendance stats retrieved', $stats);
            } elseif ($id) {
                if ($subresource === 'beneficiary') {
                    $data = $model->getBeneficiaryAttendance((int)$id);
                    apiJsonResponse(true, 'Beneficiary attendance retrieved', $data);
                } else {
                    $record = $model->getAttendanceById((int)$id);
                    if (!$record) {
                        apiJsonResponse(false, 'Attendance record not found', null, 404);
                    }
                    apiJsonResponse(true, 'Attendance record retrieved', $record);
                }
            } else {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                $dateFilter = $_GET['date'] ?? null;
                $statusFilter = $_GET['status'] ?? null;
                $beneficiaryId = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : null;

                $data = $model->getAllAttendance($limit, $offset, $dateFilter, $statusFilter, $beneficiaryId);
                apiJsonResponse(true, 'Attendance records retrieved', $data);
            }
            break;

        case 'POST':
            $input = getJsonInput();

            if (isset($input['bulk']) && $input['bulk'] === true) {
                validateRequired($input, ['session_date', 'records']);
                $results = $model->bulkRecordAttendance($input['session_date'], $input['records']);
                apiJsonResponse(true, 'Bulk attendance processed', $results);
            } else {
                validateRequired($input, ['beneficiary_id', 'session_date']);
                $aid = $model->recordAttendance(
                    $input['beneficiary_id'],
                    $input['session_date'],
                    $input['status'] ?? 'present',
                    $input['notes'] ?? null,
                    $input['meal_session_id'] ?? null
                );
                if ($aid) {
                    apiJsonResponse(true, 'Attendance recorded successfully', ['attendance_id' => (int)$aid], 201);
                }
                apiJsonResponse(false, 'Failed to record attendance', null, 500);
            }
            break;

        case 'PUT':
            if (!$id) {
                apiJsonResponse(false, 'Attendance ID is required', null, 400);
            }
            $input = getJsonInput();
            validateRequired($input, ['beneficiary_id', 'session_date', 'status']);
            $result = $model->updateAttendance(
                (int)$id,
                $input['beneficiary_id'],
                $input['session_date'],
                $input['status'],
                $input['notes'] ?? null,
                $input['meal_session_id'] ?? null
            );
            if ($result) {
                apiJsonResponse(true, 'Attendance updated successfully');
            }
            apiJsonResponse(false, 'Failed to update attendance', null, 500);
            break;

        case 'DELETE':
            if (!$id) {
                apiJsonResponse(false, 'Attendance ID is required', null, 400);
            }
            $result = $model->deleteAttendance((int)$id);
            if ($result) {
                apiJsonResponse(true, 'Attendance deleted successfully');
            }
            apiJsonResponse(false, 'Failed to delete attendance', null, 500);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
