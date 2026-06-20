<?php
/**
 * Volunteer Schedule API Endpoint
 * Returns current week's volunteer schedule
 * 
 * Endpoint: GET /api/volunteers/schedule.php
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    // Get current week schedule with volunteer names
    $stmt = $db->prepare(
        "SELECT vs.ScheduleID, vs.ScheduleDate, vs.StartTime, vs.EndTime,
                vs.Role AS `task`, vs.Location, vs.Status,
                u.FullName AS VolunteerName, u.Username
         FROM VolunteerSchedules vs
         JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
         JOIN Users u ON v.UserID = u.UserID
         WHERE vs.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
           AND vs.ScheduleDate < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 7 DAY)
         ORDER BY vs.ScheduleDate ASC, vs.StartTime ASC"
    );
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format times and add day names
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));

    foreach ($data as &$slot) {
        $dayName = date('l', strtotime($slot['ScheduleDate']));
        $slot['day'] = $dayName;
        $slot['time'] = date('H:i', strtotime($slot['StartTime'] ?? '00:00'));
        $slot['volunteer'] = $slot['VolunteerName'] ?: $slot['Username'];
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'week_label' => date('j M Y', strtotime($weekStart)) . ' - ' . date('j M Y', strtotime($weekEnd))
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteers schedule error: " . $e->getMessage(), 'ERROR');
}