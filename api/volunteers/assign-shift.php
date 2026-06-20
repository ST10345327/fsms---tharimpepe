<?php
/**
 * Volunteer Assign Shift API Endpoint
 * Assigns a shift to a volunteer or marks a completed shift
 * 
 * Endpoint: POST /api/volunteers/assign-shift.php
 * Input: { "volunteer_id": N, "date": "2026-01-15", "start_time": "08:00", "end_time": "12:00", "role": "...", "location": "...", "status": "scheduled|completed|cancelled", "hours_worked": 4.0 }
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
    if (!$input || empty($input['volunteer_id']) || empty($input['date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Volunteer ID and date are required']);
        exit();
    }

    $volunteerId = (int)$input['volunteer_id'];
    $scheduleDate = $input['date'];
    $startTime = $input['start_time'] ?? null;
    $endTime = $input['end_time'] ?? null;
    $role = trim($input['role'] ?? 'Volunteer');
    $location = trim($input['location'] ?? '');
    $shiftStatus = in_array($input['status'] ?? 'scheduled', ['scheduled', 'completed', 'cancelled', 'no-show']) ? $input['status'] : 'scheduled';
    $hoursWorked = isset($input['hours_worked']) ? (float)$input['hours_worked'] : null;
    $notes = trim($input['notes'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $scheduleDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    // Check if volunteer exists
    $check = $db->prepare("SELECT VolunteerID FROM Volunteers WHERE VolunteerID = :vid");
    $check->execute([':vid' => $volunteerId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Volunteer not found']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO VolunteerSchedules (VolunteerID, ScheduleDate, StartTime, EndTime, Role, Location, Status, HoursWorked, Notes, CreatedAt) 
         VALUES (:vid, :sdate, :start, :end, :role, :loc, :status, :hours, :notes, NOW())"
    );
    $stmt->execute([
        ':vid' => $volunteerId,
        ':sdate' => $scheduleDate,
        ':start' => $startTime,
        ':end' => $endTime,
        ':role' => $role,
        ':loc' => $location,
        ':status' => $shiftStatus,
        ':hours' => $hoursWorked,
        ':notes' => $notes
    ]);

    $scheduleId = (int)$db->lastInsertId();

    logMessage("Shift assigned: Volunteer #{$volunteerId} on {$scheduleDate} by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Shift assigned successfully',
        'data' => ['schedule_id' => $scheduleId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteers assign-shift error: " . $e->getMessage(), 'ERROR');
}