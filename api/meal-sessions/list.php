<?php
/**
 * Meal Sessions List API Endpoint
 * Returns meal session records with attendance summaries
 * 
 * Endpoint: GET /api/meal-sessions/list.php?days=30&type=Lunch
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

    $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
    $type = trim($_GET['type'] ?? '');

    $where = "ms.SessionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
    $params = [':days' => $days];

    if ($type !== '') {
        $where .= " AND ms.SessionType = :type";
        $params[':type'] = $type;
    }

    $stmt = $db->prepare(
        "SELECT ms.MealSessionID, ms.SessionDate AS `date`, ms.SessionType AS `type`,
                ms.Location, ms.Notes, ms.CreatedAt,
                COUNT(a.AttendanceID) AS total_registered,
                SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) AS present_count,
                ROUND(SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.AttendanceID), 0) * 100) AS attendance_rate
         FROM MealSession ms
         LEFT JOIN Attendance a ON ms.MealSessionID = a.MealSessionID
         WHERE {$where}
         GROUP BY ms.MealSessionID
         ORDER BY ms.SessionDate DESC, ms.SessionType ASC"
    );
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Meal sessions list error: " . $e->getMessage(), 'ERROR');
}