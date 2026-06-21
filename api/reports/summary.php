<?php
/**
 * Reports Summary API Endpoint
 * Returns aggregate statistics for the reports page
 * 
 * Endpoint: GET /api/reports/summary.php
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

    $data = [
        'meals_30d' => '—',
        'avg_daily' => '—',
        'top_volunteer' => '—',
        'top_donor' => '—',
        'stock_score' => 0
    ];

    if ($db !== null) {
        // Total meals in last 30 days
        $stmt = $db->query(
            "SELECT COUNT(*) as cnt FROM attendance WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND Status = 'present'"
        );
        $data['meals_30d'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        // Average daily attendance rate (last 30 days)
        $stmt = $db->query(
            "SELECT ROUND(AVG(rate)) as avg_rate FROM (
                SELECT SessionDate, 
                       SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) / COUNT(*) * 100 AS rate
                FROM attendance
                WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY SessionDate
            ) daily"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['avg_daily'] = $row['avg_rate'] ? (int)$row['avg_rate'] : '—';

        // Most active volunteer
        $stmt = $db->query(
            "SELECT u.FullName, COUNT(vs.ScheduleID) as shifts
             FROM volunteerschedules vs
             JOIN volunteers v ON vs.VolunteerID = v.VolunteerID
             JOIN users u ON v.UserID = u.UserID
             WHERE vs.STATUS = 'completed'
             GROUP BY v.VolunteerID
             ORDER BY shifts DESC LIMIT 1"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['top_volunteer'] = $row ? $row['FullName'] : '—';

        // Top donor this month
        $stmt = $db->query(
            "SELECT DonorName, COUNT(*) as donations
             FROM donations
             WHERE MONTH(DonationDate) = MONTH(CURDATE()) AND YEAR(DonationDate) = YEAR(CURDATE())
             GROUP BY DonorName
             ORDER BY donations DESC LIMIT 1"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['top_donor'] = $row ? $row['DonorName'] : '—';

        // Stock health score (percentage of items with sufficient stock > 25)
        $stmt = $db->query(
            "SELECT ROUND(SUM(CASE WHEN Quantity > 25 THEN 1 ELSE 0 END) / COUNT(*) * 100) as score
             FROM foodstock WHERE Quantity > 0"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['stock_score'] = $row ? (int)$row['score'] : 0;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Reports summary error: " . $e->getMessage(), 'ERROR');
}