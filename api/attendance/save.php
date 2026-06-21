<?php
/**
 * Attendance Save API Endpoint
 * Saves attendance records for a given date
 * 
 * Endpoint: POST /api/attendance/save.php
 * Input: { "date": "2026-01-15", "attendance": [{"id": 1, "status": "present"}, ...] }
 * Auth: Bearer token required
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

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['date']) || empty($input['attendance']) || !is_array($input['attendance'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Date and attendance array required']);
        exit();
    }

    $date = $input['date'];
    $attendance = $input['attendance'];

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    $db->beginTransaction();
    $inserted = 0;
    $updated = 0;

    $checkStmt = $db->prepare(
        "SELECT AttendanceID FROM attendance WHERE BeneficiaryID = :bid AND SessionDate = :sdate"
    );
    $updateStmt = $db->prepare(
        "UPDATE attendance SET Status = :status, Notes = :notes WHERE AttendanceID = :aid"
    );
    $insertStmt = $db->prepare(
        "INSERT INTO attendance (BeneficiaryID, SessionDate, Status, Notes, CreatedAt)
         VALUES (:bid, :sdate, :status, :notes, NOW())"
    );

    foreach ($attendance as $record) {
        if (empty($record['id'])) continue;

        $beneficiaryId = (int)$record['id'];
        $rawStatus = strtolower(trim($record['status'] ?? 'absent'));
        $statusMap = ['late' => 'marked', 'excused' => 'marked'];
        $status = $statusMap[$rawStatus] ?? $rawStatus;
        if (!in_array($status, ['present', 'absent', 'marked'], true)) {
            $status = 'absent';
        }
        $notes = $record['notes'] ?? '';

        // Check if record exists
        $checkStmt->execute([':bid' => $beneficiaryId, ':sdate' => $date]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $updateStmt->execute([
                ':status' => $status,
                ':notes' => $notes,
                ':aid' => $existing['AttendanceID']
            ]);
            $updated++;
        } else {
            $insertStmt->execute([
                ':bid' => $beneficiaryId,
                ':sdate' => $date,
                ':status' => $status,
                ':notes' => $notes
            ]);
            $inserted++;
        }
    }

    $db->commit();

    logMessage("Attendance saved for {$date}: {$inserted} new, {$updated} updated by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Attendance saved successfully',
        'data' => ['inserted' => $inserted, 'updated' => $updated]
    ]);

} catch (Exception $e) {
    if ($db !== null && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance save error: " . $e->getMessage(), 'ERROR');
}