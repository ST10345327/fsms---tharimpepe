<?php
/**
 * Volunteer Status Update API Endpoint
 * Updates a volunteer's approval status or availability
 * 
 * Endpoint: POST /api/volunteers/status.php
 * Input: { "volunteer_id": N, "status": "approved|rejected", "availability": "available|unavailable|on_leave" }
 * Auth: Bearer token required (admin/staff for status changes)
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
    if (!$input || empty($input['volunteer_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Volunteer ID is required']);
        exit();
    }

    $volunteerId = (int)$input['volunteer_id'];
    $updates = [];
    $params = [':vid' => $volunteerId];

    // Only admin/staff can change approval status
    if (isset($input['status']) && in_array($input['status'], ['pending', 'approved', 'rejected', 'inactive'])) {
        if (!in_array($user['role'], ['admin', 'staff'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only admins can change volunteer status']);
            exit();
        }
        $updates[] = "Status = :status";
        $params[':status'] = $input['status'];

        if ($input['status'] === 'approved') {
            $updates[] = "ApprovedBy = :approved_by";
            $updates[] = "ApprovedAt = NOW()";
            $params[':approved_by'] = $user['user_id'];
        }
    }

    // User can change own availability
    if (isset($input['availability']) && in_array($input['availability'], ['available', 'unavailable', 'on_leave'])) {
        $updates[] = "AvailabilityStatus = :avail";
        $params[':avail'] = $input['availability'];
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        exit();
    }

    $updates[] = "UpdatedAt = NOW()";
    $sql = "UPDATE Volunteers SET " . implode(', ', $updates) . " WHERE VolunteerID = :vid";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    logMessage("Volunteer #{$volunteerId} status updated by user '{$user['username']}'", 'INFO');

    echo json_encode(['success' => true, 'message' => 'Volunteer status updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Volunteers status error: " . $e->getMessage(), 'ERROR');
}