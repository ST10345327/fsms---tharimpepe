<?php
/**
 * Reports History API
 * Returns recently generated/exported reports from the activity log.
 *
 * Endpoint: GET /api/reports/history.php?limit=5
 * Auth: Bearer token required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

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

    $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
    $reports = [];

    if ($db !== null) {
        try {
            $stmt = $db->prepare("
                SELECT al.ActivityLogID AS log_id,
                       al.Action AS action,
                       al.AffectedEntityName AS entity_name,
                       al.AffectedEntityID AS entity_id,
                       al.Details AS details,
                       al.Timestamp AS created_at,
                       u.Username AS username
                FROM activitylog al
                LEFT JOIN users u ON al.UserID = u.UserID
                WHERE al.Action IN ('generate_report', 'export_report')
                   OR al.AffectedEntityName = 'Report'
                ORDER BY al.Timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $type = $row['entity_id'] ?: 'report';
                $title = $row['details'] ?: ucfirst($type) . ' Report';
                $logId = (int)$row['log_id'];

                $reports[] = [
                    'id' => $type . ':' . $logId,
                    'report_id' => $logId,
                    'type' => $type,
                    'title' => $title,
                    'date' => date('Y-m-d', strtotime($row['created_at'])),
                    'created_at' => $row['created_at'],
                    'action' => $row['action'],
                    'generated_by' => $row['username'] ?? 'system'
                ];
            }
        } catch (Exception $e) {
            $reports = [];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $reports,
        'meta' => ['count' => count($reports), 'timestamp' => date('c')]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage('Reports history error: ' . $e->getMessage(), 'ERROR');
}
