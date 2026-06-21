<?php
/**
 * Attendance Analytics API
 * Returns attendance data for charts and analytics
 * 
 * Endpoint: GET /api/attendance/analytics.php?period=7d|30d|90d
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "daily": [
 *       {"date": "2026-01-15", "present": 45, "absent": 5, "late": 3, "total": 53, "rate": "84.9%"}
 *     ],
 *     "summary": {
 *       "total_sessions": 20,
 *       "avg_present": 42,
 *       "avg_absent": 6,
 *       "avg_rate": "87.5%"
 *     }
 *   }
 * }
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

    $period = isset($_GET['period']) ? $_GET['period'] : '7d';
    $days = 7;
    if ($period === '30d') $days = 30;
    elseif ($period === '90d') $days = 90;

    $daily = [];
    $summary = [
        'total_sessions' => 0,
        'avg_present' => 0,
        'avg_absent' => 0,
        'avg_rate' => '0%'
    ];

    if ($db !== null) {
        try {
            $stmt = $db->prepare("
                SELECT 
                    a.SessionDate,
                    COUNT(*) as total,
                    SUM(CASE WHEN LOWER(a.Status) = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN LOWER(a.Status) = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN LOWER(a.Status) = 'late' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN LOWER(a.Status) = 'excused' THEN 1 ELSE 0 END) as excused
                FROM attendance a
                WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY a.SessionDate
                ORDER BY a.SessionDate ASC
            ");
            $stmt->execute([':days' => $days]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $total = (int)$row['total'];
                $present = (int)$row['present'];
                $rate = $total > 0 ? round(($present / $total) * 100, 1) . '%' : '0%';

                $daily[] = [
                    'date' => $row['SessionDate'],
                    'present' => $present,
                    'absent' => (int)$row['absent'],
                    'late' => (int)$row['late'],
                    'excused' => (int)$row['excused'],
                    'total' => $total,
                    'rate' => $rate
                ];
            }

            // Calculate summary
            $summary['total_sessions'] = count($daily);
            if ($summary['total_sessions'] > 0) {
                $summary['avg_present'] = (int)array_sum(array_column($daily, 'present')) / $summary['total_sessions'];
                $summary['avg_absent'] = (int)array_sum(array_column($daily, 'absent')) / $summary['total_sessions'];
                
                $rates = array_map(function($d) {
                    return (float)rtrim($d['rate'], '%');
                }, $daily);
                $avgRate = array_sum($rates) / count($rates);
                $summary['avg_rate'] = round($avgRate, 1) . '%';
            }
        } catch (Exception $e) {
            $daily = [];
        }
    }

    logMessage("Attendance analytics fetched by user '{$user['username']}' - $period", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => [
            'daily' => $daily,
            'summary' => $summary,
            'period' => $period,
            'days' => $days
        ],
        'meta' => ['timestamp' => date('c')]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance analytics error: " . $e->getMessage(), 'ERROR');
}