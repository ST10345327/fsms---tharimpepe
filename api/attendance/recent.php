<?php
/**
 * Recent Attendance API Endpoint
 * Returns recent attendance records for dashboard
 * 
 * Endpoint: GET /api/attendance/recent.php?limit=5
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

    $limit = min(20, max(1, (int)($_GET['limit'] ?? 5)));

    if ($db === null) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }

    $stmt = $db->prepare(
        "SELECT a.AttendanceID, a.SessionDate AS `date`, a.Status,
                CONCAT(b.FirstName, ' ', b.LastName) AS `name`,
                b.BeneficiaryID
         FROM Attendance a
         JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
         WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         ORDER BY a.SessionDate DESC, a.AttendanceID DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance recent error: " . $e->getMessage(), 'ERROR');
}