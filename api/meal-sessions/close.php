<?php
/**
 * Meal Session Close API Endpoint
 * Closes a meal session and marks unmarked beneficiaries as absent
 * 
 * Endpoint: POST /api/meal-sessions/close.php
 * Input: { "session_id": N, "date": "2026-01-15", "type": "Lunch" }
 * Auth: Bearer token required (admin/staff)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireRole(['admin', 'staff']);

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || (empty($input['session_id']) && (empty($input['date']) || empty($input['type'])))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Session ID or date+type required']);
        exit();
    }

    // Determine which session to close
    if (!empty($input['session_id'])) {
        $sessionId = (int)$input['session_id'];
        $stmt = $db->prepare("SELECT MealSessionID, SessionDate FROM MealSession WHERE MealSessionID = :id");
        $stmt->execute([':id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$session) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Session not found']);
            exit();
        }
        $closeDate = $session['SessionDate'];
    } else {
        $closeDate = $input['date'];
        $sessionType = trim($input['type']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $closeDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            exit();
        }
    }

    // Mark all active beneficiaries without attendance for this date as absent
    $stmt = $db->prepare(
        "INSERT INTO Attendance (BeneficiaryID, SessionDate, Status, Notes, CreatedAt)
         SELECT b.BeneficiaryID, :date, 'absent', 'Auto-marked absent on session close', NOW()
         FROM Beneficiaries b
         WHERE b.Status = 'active'
           AND b.BeneficiaryID NOT IN (
               SELECT a.BeneficiaryID FROM Attendance a 
               WHERE a.SessionDate = :date2
           )"
    );
    $stmt->execute([':date' => $closeDate, ':date2' => $closeDate]);
    $markedAbsent = $stmt->rowCount();

    logMessage("Meal session closed for {$closeDate}: {$markedAbsent} marked absent by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Session closed successfully',
        'data' => ['marked_absent' => $markedAbsent]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Meal sessions close error: " . $e->getMessage(), 'ERROR');
}