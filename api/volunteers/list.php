<?php
/**
 * Volunteers List API Endpoint
 * Returns list of all volunteers with their details
 * 
 * Endpoint: GET /api/volunteers/list.php?status=approved&availability=available
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

    $status = trim($_GET['status'] ?? '');
    $availability = trim($_GET['availability'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $where = [];
    $params = [];

    if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected', 'inactive'])) {
        $where[] = "v.Status = :status";
        $params[':status'] = $status;
    }

    if ($availability !== '' && in_array($availability, ['available', 'unavailable', 'on_leave'])) {
        $where[] = "v.AvailabilityStatus = :avail";
        $params[':avail'] = $availability;
    }

    if ($search !== '') {
        $where[] = "(u.FullName LIKE :search OR u.Username LIKE :search2)";
        $params[':search'] = "%{$search}%";
        $params[':search2'] = "%{$search}%";
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $db->prepare(
        "SELECT v.VolunteerID, u.UserID, u.FullName, u.Username, u.Email, u.Phone,
                v.Skills, v.AvailabilityStatus, v.Status AS volunteer_status,
                v.Notes, v.ApprovedAt, v.CreatedAt,
                COUNT(vs.ScheduleID) AS total_shifts,
                COALESCE(SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END), 0) AS completed_shifts,
                COALESCE(SUM(vs.HoursWorked), 0) AS total_hours
         FROM Volunteers v
         JOIN Users u ON v.UserID = u.UserID
         LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
         {$whereClause}
         GROUP BY v.VolunteerID
         ORDER BY u.FullName ASC"
    );
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteers list error: " . $e->getMessage(), 'ERROR');
}