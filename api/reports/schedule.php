<?php
/**
 * Reports Schedule API
 * Create and manage scheduled/automated reports
 * 
 * Endpoint: GET /api/reports/schedule.php (list)
 *           POST /api/reports/schedule.php (create)
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        listScheduledReports($db, $user);
    } else {
        createScheduledReport($db, $user);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Reports schedule error: " . $e->getMessage(), 'ERROR');
}

function listScheduledReports($db, $user) {
    $schedules = [];

    if ($db !== null) {
        try {
            $stmt = $db->query("
                SELECT ScheduleID, ReportType, ReportName, Frequency, 
                       Recipients, LastRun, NextRun, IsActive, CreatedAt, UpdatedAt
                FROM ReportSchedules
                WHERE IsActive = 1 OR IsActive IS NULL
                ORDER BY NextRun ASC
                LIMIT 50
            ");
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $schedules = [];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $schedules,
        'meta' => ['count' => count($schedules), 'timestamp' => date('c')]
    ]);
}

function createScheduledReport($db, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['report_type']) || !isset($input['frequency'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'report_type and frequency are required']);
        exit();
    }

    $reportType = strtolower($input['report_type']);
    $frequency = strtolower($input['frequency']);
    $reportName = $input['report_name'] ?? ucfirst($reportType) . ' Report';
    $recipients = $input['recipients'] ?? '';
    $from = $input['from'] ?? '';
    $to = $input['to'] ?? '';
    $format = strtolower($input['format'] ?? 'csv');
    $userId = $user['user_id'] ?? $user['id'] ?? 0;

    $validTypes = ['beneficiaries', 'attendance', 'stock', 'volunteers', 'donations', 'financial'];
    $validFrequencies = ['daily', 'weekly', 'monthly', 'quarterly'];

    if (!in_array($reportType, $validTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
    }

    if (!in_array($frequency, $validFrequencies, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid frequency']);
        exit();
    }

    $nextRun = calculateNextRun($frequency);
    $scheduleId = null;

    if ($db !== null) {
        try {
            $stmt = $db->prepare("
                INSERT INTO ReportSchedules 
                    (ReportType, ReportName, Frequency, Recipients, DateFrom, DateTo, 
                     ExportFormat, CreatedBy, NextRun, IsActive, CreatedAt, UpdatedAt)
                VALUES 
                    (:type, :name, :freq, :recip, :from, :to, :format, :uid, :next, 1, NOW(), NOW())
            ");
            $stmt->execute([
                ':type' => $reportType,
                ':name' => $reportName,
                ':freq' => $frequency,
                ':recip' => $recipients,
                ':from' => $from ?: null,
                ':to' => $to ?: null,
                ':format' => $format,
                ':uid' => $userId,
                ':next' => $nextRun
            ]);
            $scheduleId = $db->lastInsertId();
        } catch (Exception $e) {
            logMessage("Report schedule insert failed: " . $e->getMessage(), 'ERROR');
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Report scheduling is not available (database table missing)']);
            exit();
        }
    }

    logMessage("Report schedule created by user '{$user['username']}' - $reportType ($frequency)", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Report scheduled successfully',
        'data' => [
            'schedule_id' => $scheduleId,
            'report_type' => $reportType,
            'report_name' => $reportName,
            'frequency' => $frequency,
            'next_run' => $nextRun,
            'recipients' => $recipients,
            'format' => $format
        ]
    ]);
}

function calculateNextRun($frequency) {
    $now = new DateTime();

    switch ($frequency) {
        case 'daily':
            $now->modify('+1 day');
            break;
        case 'weekly':
            $now->modify('+1 week');
            break;
        case 'monthly':
            $now->modify('+1 month');
            break;
        case 'quarterly':
            $now->modify('+3 months');
            break;
        default:
            $now->modify('+1 week');
    }

    return $now->format('Y-m-d H:i:s');
}
