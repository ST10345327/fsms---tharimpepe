<?php
/**
 * Meal Sessions API
 * Returns available meal sessions for attendance marking
 * 
 * Endpoint: GET /api/attendance/sessions.php
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": [
 *     {"MealSessionID": 1, "SessionName": "Breakfast", "SessionTime": "08:00", "IsActive": true},
 *     {"MealSessionID": 2, "SessionName": "Lunch", "SessionTime": "12:00", "IsActive": true}
 *   ]
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
require_once __DIR__ . '/../../app/helpers/AuditLogger.php';

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();

    $sessions = [];

    if ($db !== null) {
        try {
            $stmt = $db->query("
                SELECT MealSessionID, SessionType as SessionName, CreatedAt as SessionTime, 1 as IsActive, Notes as Description
                FROM mealsession
                ORDER BY CreatedAt ASC
            ");
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            AuditLogger::log('meal_sessions_fallback', 'attendance', 'meal_session', null, [
                'actor_user_id' => $user['user_id'],
                'actor_username' => $user['username'],
                'actor_role' => $user['role'],
                'response_status' => 200,
                'is_success' => true,
                'severity' => 'warning',
                'failure_reason' => 'Using fallback sessions: ' . $e->getMessage()
            ]);

            // Fallback if table doesn't exist
            $sessions = [
                ['MealSessionID' => 1, 'SessionName' => 'Breakfast', 'SessionTime' => '08:00', 'IsActive' => true],
                ['MealSessionID' => 2, 'SessionName' => 'Lunch', 'SessionTime' => '12:00', 'IsActive' => true],
                ['MealSessionID' => 3, 'SessionName' => 'Dinner', 'SessionTime' => '17:00', 'IsActive' => true]
            ];
        }
    }

    AuditLogger::log('meal_sessions_fetched', 'attendance', 'meal_session', null, [
        'actor_user_id' => $user['user_id'],
        'actor_username' => $user['username'],
        'actor_role' => $user['role'],
        'response_status' => 200,
        'is_success' => true,
        'severity' => 'info',
        'metadata' => ['count' => count($sessions)]
    ]);

    logMessage("Meal sessions fetched by user '{$user['username']}'", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $sessions,
        'meta' => ['count' => count($sessions), 'timestamp' => date('c')]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    
    if (isset($user)) {
        AuditLogger::log('meal_sessions_error', 'attendance', 'meal_session', null, [
            'actor_user_id' => $user['user_id'],
            'actor_username' => $user['username'],
            'actor_role' => $user['role'],
            'response_status' => 500,
            'is_success' => false,
            'failure_reason' => $e->getMessage(),
            'severity' => 'error'
        ]);
    }
    
    logMessage("Meal sessions error: " . $e->getMessage(), 'ERROR');
}