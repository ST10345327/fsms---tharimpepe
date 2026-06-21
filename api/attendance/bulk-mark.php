<?php
/**
 * Bulk Attendance Mark API
 * Handles bulk marking of attendance for multiple beneficiaries
 * 
 * Endpoint: POST /api/attendance/bulk-mark.php
 * Auth: Bearer token required
 * Input: {
 *   "date": "2026-01-15",
 *   "session_id": 1,
 *   "attendance": [
 *     {"beneficiary_id": 1, "status": "present"},
 *     {"beneficiary_id": 2, "status": "absent"},
 *     {"beneficiary_id": 3, "status": "late"}
 *   ]
 * }
 * Output: {
 *   "success": true,
 *   "data": {
 *     "total": 3,
 *     "created": 3,
 *     "updated": 0,
 *     "errors": []
 *   }
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../app/helpers/AuditLogger.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireRole(['admin', 'staff', 'volunteer']);

    // Get raw input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['attendance']) || !is_array($input['attendance'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input: attendance array required']);
        exit();
    }

    $date = isset($input['date']) ? $input['date'] : date('Y-m-d');
    $sessionId = isset($input['session_id']) ? (int)$input['session_id'] : null;
    $attendanceRecords = $input['attendance'];

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database not available']);
        exit();
    }

    $results = [
        'total' => count($attendanceRecords),
        'created' => 0,
        'updated' => 0,
        'errors' => []
    ];

    $db->beginTransaction();

    try {
        foreach ($attendanceRecords as $record) {
            $beneficiaryId = isset($record['beneficiary_id']) ? (int)$record['beneficiary_id'] : 0;
            $status = isset($record['status']) ? strtolower(trim($record['status'])) : 'absent';

            // Validate status (schema ENUM: present, absent, marked)
            $statusMap = ['late' => 'marked', 'excused' => 'marked'];
            $validStatuses = ['present', 'absent', 'late', 'excused', 'marked'];
            if (!in_array($status, $validStatuses, true)) {
                $results['errors'][] = "Invalid status for beneficiary $beneficiaryId: $status";
                continue;
            }
            if (isset($statusMap[$status])) {
                $status = $statusMap[$status];
            }

            if (!$beneficiaryId) {
                $results['errors'][] = 'Missing beneficiary_id';
                continue;
            }

            // Check if record exists
            $checkSql = "SELECT AttendanceID FROM attendance
                         WHERE BeneficiaryID = :bid AND SessionDate = :date";
            $params = [':bid' => $beneficiaryId, ':date' => $date];
            if ($sessionId) {
                $checkSql .= " AND MealSessionID = :sid";
                $params[':sid'] = $sessionId;
            }

            $stmt = $db->prepare($checkSql);
            $stmt->execute($params);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $updateSql = "UPDATE attendance SET Status = :status WHERE AttendanceID = :aid";
                $stmt = $db->prepare($updateSql);
                $stmt->execute([
                    ':status' => $status,
                    ':aid' => $existing['AttendanceID']
                ]);
                $results['updated']++;
            } else {
                $insertSql = "INSERT INTO attendance
                              (BeneficiaryID, SessionDate, Status, MealSessionID, CreatedAt)
                              VALUES (:bid, :date, :status, :sid, NOW())";
                $stmt = $db->prepare($insertSql);
                $stmt->execute([
                    ':bid' => $beneficiaryId,
                    ':date' => $date,
                    ':status' => $status,
                    ':sid' => $sessionId
                ]);
                $results['created']++;
            }
        }

        $db->commit();

        AuditLogger::log('attendance_bulk_mark', 'attendance', 'attendance', null, [
            'actor_user_id' => $user['user_id'],
            'actor_username' => $user['username'],
            'actor_role' => $user['role'],
            'response_status' => 200,
            'is_success' => true,
            'severity' => 'info',
            'metadata' => [
                'total' => $results['total'],
                'created' => $results['created'],
                'updated' => $results['updated']
            ]
        ]);

        logMessage("Bulk attendance marked by user '{$user['username']}' - {$results['total']} records", 'INFO');
        echo json_encode([
            'success' => true,
            'data' => $results,
            'meta' => ['timestamp' => date('c')]
        ]);

    } catch (Exception $e) {
        $db->rollBack();

        AuditLogger::log('attendance_bulk_mark_failed', 'attendance', 'attendance', null, [
            'actor_user_id' => $user['user_id'],
            'actor_username' => $user['username'],
            'actor_role' => $user['role'],
            'response_status' => 500,
            'is_success' => false,
            'failure_reason' => $e->getMessage(),
            'severity' => 'error'
        ]);

        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Bulk attendance error: " . $e->getMessage(), 'ERROR');
}
