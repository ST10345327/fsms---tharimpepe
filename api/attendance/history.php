<?php
/**
 * Attendance History API Endpoint
 * Returns daily attendance summary for history view
 * 
 * Endpoint: GET /api/attendance/history.php?days=30
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

    $days = min(90, max(1, (int)($_GET['days'] ?? 30)));

    if ($db === null) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    $stmt = $db->prepare(
        "SELECT a.SessionDate AS `date`,
                COUNT(*) AS registered,
                SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) AS present,
                ROUND(SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) / COUNT(*) * 100) AS rate
         FROM Attendance a
         WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
         GROUP BY a.SessionDate
         ORDER BY a.SessionDate DESC"
    );
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance history error: " . $e->getMessage(), 'ERROR');
}