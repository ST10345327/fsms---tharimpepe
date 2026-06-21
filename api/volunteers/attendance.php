<?php
/**
 * Volunteer Attendance API
 * Returns attendance records for volunteers with stats
 * 
 * Endpoint: GET /api/volunteers/attendance.php?volunteer_id=123&from=2026-01-01&to=2026-01-31
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "records": [...],
 *     "summary": {
 *       "total_scheduled": 20,
 *       "attended": 18,
 *       "missed": 2,
 *       "rate": "90%",
 *       "total_hours": 36
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

    $volunteerId = isset($_GET['volunteer_id']) ? (int)$_GET['volunteer_id'] : 0;
    $from = isset($_GET['from']) ? $_GET['from'] : '';
    $to = isset($_GET['to']) ? $_GET['to'] : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;

    $where = ["vs.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)"];
    $params = [];

    if ($volunteerId) {
        $where[] = "vs.VolunteerID = :volunteer_id";
        $params[':volunteer_id'] = $volunteerId;
    }

    if ($from) {
        $where[] = "vs.ScheduleDate >= :from";
        $params[':from'] = $from;
    }

    if ($to) {
        $where[] = "vs.ScheduleDate <= :to";
        $params[':to'] = $to;
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $records = [];
    $summary = [
        'total_scheduled' => 0,
        'attended' => 0,
        'missed' => 0,
        'late' => 0,
        'rate' => '0%',
        'total_hours' => 0
    ];

    if ($db !== null) {
        try {
            // Fetch attendance records
            $sql = "
                SELECT 
                    vs.ScheduleID,
                    vs.VolunteerID,
                    u.FullName AS volunteer_name,
                    vs.ScheduleDate,
                    vs.StartTime,
                    vs.EndTime,
                    vs.Role AS task,
                    vs.Location,
                    vs.Status AS schedule_status,
                    va.CheckInTime,
                    va.CheckOutTime,
                    va.Status AS attendance_status,
                    va.Notes,
                    COALESCE(vs.HoursWorked, 0) AS hours_worked
                FROM VolunteerSchedules vs
                JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
                JOIN Users u ON v.UserID = u.UserID
                LEFT JOIN VolunteerAttendance va ON vs.ScheduleID = va.ScheduleID
                $whereClause
                ORDER BY vs.ScheduleDate DESC, vs.StartTime ASC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate summary
            $summary['total_scheduled'] = count($records);
            foreach ($records as $rec) {
                $attStatus = strtolower($rec['attendance_status'] ?? 'absent');
                if ($attStatus === 'present' || $attStatus === 'completed') {
                    $summary['attended']++;
                } elseif ($attStatus === 'late') {
                    $summary['late']++;
                } else {
                    $summary['missed']++;
                }
                $summary['total_hours'] += (float)$rec['hours_worked'];
            }

            if ($summary['total_scheduled'] > 0) {
                $rate = ($summary['attended'] / $summary['total_scheduled']) * 100;
                $summary['rate'] = round($rate) . '%';
            }
        } catch (Exception $e) {
            $records = [];
        }
    }

    logMessage("Volunteer attendance fetched by user '{$user['username']}' - " . count($records) . " records", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => [
            'records' => $records,
            'summary' => $summary
        ],
        'meta' => [
            'count' => count($records),
            'page' => $page,
            'limit' => $limit,
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteer attendance error: " . $e->getMessage(), 'ERROR');
}