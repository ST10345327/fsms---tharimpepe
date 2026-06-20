<?php
/**
 * Meal Session Create API Endpoint
 * Creates a new meal session for a given date/type
 * 
 * Endpoint: POST /api/meal-sessions/create.php
 * Input: { "date": "2026-01-15", "type": "Breakfast|Lunch|Dinner", "location": "...", "notes": "..." }
 * Auth: Bearer token required
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
    $user = $auth->requireAuth();

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database unavailable']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['date']) || empty($input['type'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Date and session type are required']);
        exit();
    }

    $sessionDate = $input['date'];
    $sessionType = trim($input['type']);
    $location = trim($input['location'] ?? 'Main Hall');
    $notes = trim($input['notes'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sessionDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format (YYYY-MM-DD)']);
        exit();
    }

    // Check for duplicate session
    $check = $db->prepare(
        "SELECT MealSessionID FROM MealSession WHERE SessionDate = :date AND SessionType = :type AND Location = :loc"
    );
    $check->execute([':date' => $sessionDate, ':type' => $sessionType, ':loc' => $location]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'A session with this date, type, and location already exists']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO MealSession (SessionDate, SessionType, Location, Notes, CreatedAt) 
         VALUES (:date, :type, :loc, :notes, NOW())"
    );
    $stmt->execute([
        ':date' => $sessionDate,
        ':type' => $sessionType,
        ':loc' => $location,
        ':notes' => $notes
    ]);

    $sessionId = (int)$db->lastInsertId();

    logMessage("Meal session created: {$sessionType} on {$sessionDate} by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Meal session created successfully',
        'data' => ['session_id' => $sessionId]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Meal sessions create error: " . $e->getMessage(), 'ERROR');
}