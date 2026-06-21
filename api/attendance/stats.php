<?php
/**
 * Attendance Statistics API
 * Endpoint: GET /api/attendance/stats.php
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
    $auth->requireAuth();

    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $stats = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'marked' => 0, 'date' => $date];

    if ($db !== null) {
        $stmt = $db->prepare("
            SELECT LOWER(Status) as status, COUNT(*) as cnt
            FROM attendance
            WHERE SessionDate = :date
            GROUP BY LOWER(Status)
        ");
        $stmt->execute([':date' => $date]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'] ?? '';
            $count = (int)$row['cnt'];
            $stats['total'] += $count;
            if ($status === 'marked') {
                $stats['late'] += $count;
                $stats['marked'] += $count;
            } elseif (isset($stats[$status])) {
                $stats[$status] += $count;
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $stats]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance stats error: " . $e->getMessage(), 'ERROR');
}
