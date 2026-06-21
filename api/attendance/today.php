<?php
/**
 * Attendance Today API Endpoint
 * Returns today's attendance status for all active beneficiaries
 * 
 * Endpoint: GET /api/attendance/today.php
 * Auth: Bearer token required
 * Output: { "success": true, "data": [...] }
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
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    // Get all active beneficiaries with today's attendance status
    $stmt = $db->prepare(
        "SELECT b.BeneficiaryID, b.FirstName, b.LastName, 
                CONCAT(b.FirstName, ' ', b.LastName) AS FullName,
                a.Status, a.AttendanceID,
                CASE 
                    WHEN b.Age < 18 THEN 'Child' 
                    WHEN b.Age >= 60 THEN 'Elderly' 
                    ELSE 'Adult' 
                END AS Category
         FROM beneficiaries b
         LEFT JOIN attendance a ON b.BeneficiaryID = a.BeneficiaryID
            AND a.SessionDate = CURDATE()
         WHERE b.Status = 'active'
         ORDER BY b.LastName ASC, b.FirstName ASC"
    );
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalize status: NULL (not marked) stays as empty string for frontend
    foreach ($data as &$row) {
        $row['status'] = $row['Status'] ?? '';
        unset($row['Status']);
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance today error: " . $e->getMessage(), 'ERROR');
}