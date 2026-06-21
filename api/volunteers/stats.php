<?php
/**
 * Volunteer Statistics API
 * Endpoint: GET /api/volunteers/stats.php
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

    $stats = [
        'total' => 0,
        'approved' => 0,
        'pending' => 0,
        'available' => 0,
        'scheduled_today' => 0
    ];

    if ($db !== null) {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM volunteers");
        $stats['total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        $stmt = $db->query("SELECT LOWER(Status) as status, COUNT(*) as cnt FROM volunteers GROUP BY LOWER(Status)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'] ?? '';
            if (isset($stats[$status])) {
                $stats[$status] = (int)$row['cnt'];
            }
        }

        $stmt = $db->query("SELECT COUNT(*) as cnt FROM volunteers WHERE AvailabilityStatus = 'available' AND Status = 'approved'");
        $stats['available'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        $stmt = $db->query("SELECT COUNT(DISTINCT VolunteerID) as cnt FROM volunteerschedules WHERE ScheduleDate = CURDATE() AND Status = 'scheduled'");
        $stats['scheduled_today'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    }

    echo json_encode(['success' => true, 'data' => $stats]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteer stats error: " . $e->getMessage(), 'ERROR');
}
