<?php
/**
 * Dashboard Activity API Endpoint
 * Returns recent activity feed across all modules
 * 
 * Endpoint: GET /api/dashboard/activity.php
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": [
 *     {
 *       "id": 1,
 *       "type": "attendance|beneficiary|donation|stock|volunteer|system",
 *       "action": "marked_present|registered|donated|updated|scheduled|...",
 *       "title": "User-friendly title",
 *       "description": "Details about the activity",
 *       "user": "Admin Name",
 *       "entity_id": 123,
 *       "entity_type": "beneficiary|donation|volunteer|stock",
 *       "icon": "fa-clipboard-check|fa-user-plus|...",
 *       "created_at": "2026-01-15T10:30:00Z"
 *     }
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

try {
    $db = getDBConnection();
    $auth = new AuthMiddleware($db);
    $user = $auth->requireAuth();
    $activities = [];

    if ($db !== null) {
        // --- RECENT ATTENDANCE ACTIVITY ---
        try {
            $stmt = $db->query("
                SELECT a.AttendanceID, a.Status, a.SessionDate,
                       b.FirstName, b.LastName, b.BeneficiaryID,
                       u.username as recorded_by
                FROM attendance a
                JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                LEFT JOIN users u ON a.RecordedBy = u.UserID
                WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY a.CreatedAt DESC
                LIMIT 10
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = strtolower($row['Status'] ?? 'present');
                $validStatuses = ['present', 'absent', 'late', 'excused'];
                if (!in_array($status, $validStatuses, true)) {
                    $status = 'present';
                }
                $label = ucfirst($status);
                $icon = $status === 'present' ? 'fa-clipboard-check' : ($status === 'absent' ? 'fa-user-xmark' : 'fa-clock');
                $activities[] = [
                    'id' => 'att_' . (int)$row['AttendanceID'],
                    'type' => 'attendance',
                    'action' => 'marked_' . $status,
                    'title' => $label . ': ' . $row['FirstName'] . ' ' . $row['LastName'],
                    'description' => 'Attendance recorded on ' . date('M j, Y', strtotime($row['SessionDate'])) . ' by ' . ($row['recorded_by'] ?? 'system'),
                    'user' => $row['recorded_by'] ?? 'system',
                    'entity_id' => (int)$row['AttendanceID'],
                    'entity_type' => 'attendance',
                    'icon' => $icon,
                    'created_at' => date('c', strtotime($row['SessionDate']))
                ];
            }
        } catch (Exception $e) { /* attendance may not be fully set up */ }

        // --- RECENT BENEFICIARY REGISTRATIONS ---
        try {
            $stmt = $db->query("
                SELECT BeneficiaryID, FirstName, LastName, Status, CreatedAt,
                       u.username as created_by
                FROM beneficiaries b
                LEFT JOIN users u ON b.CreatedBy = u.UserID
                WHERE CreatedAt >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                ORDER BY CreatedAt DESC
                LIMIT 10
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = strtolower($row['Status'] ?? 'active');
                $validStatuses = ['active', 'inactive', 'pending', 'suspended'];
                if (!in_array($status, $validStatuses, true)) {
                    $status = 'active';
                }
                $label = ucfirst($status);
                $icon = $status === 'active' ? 'fa-user-check' : 'fa-user-plus';
                $activities[] = [
                    'id' => 'ben_' . (int)$row['BeneficiaryID'],
                    'type' => 'beneficiary',
                    'action' => 'registered',
                    'title' => $label . ' Beneficiary: ' . $row['FirstName'] . ' ' . $row['LastName'],
                    'description' => 'Registered on ' . date('M j, Y', strtotime($row['CreatedAt'])) . ' by ' . ($row['created_by'] ?? 'system'),
                    'user' => $row['created_by'] ?? 'system',
                    'entity_id' => (int)$row['BeneficiaryID'],
                    'entity_type' => 'beneficiary',
                    'icon' => $icon,
                    'created_at' => date('c', strtotime($row['CreatedAt']))
                ];
            }
        } catch (Exception $e) { /* beneficiaries may not have CreatedBy */ }

        // --- RECENT DONATIONS ---
        try {
            $stmt = $db->query("
                SELECT d.DonationID, d.DonationType, d.Amount, d.DonationDate,
                       d.DonorName,
                       u.Username as recorded_by
                FROM donations d
                LEFT JOIN users u ON d.UserID = u.UserID
                WHERE d.DonationDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                ORDER BY d.DonationDate DESC
                LIMIT 10
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $icon = 'fa-hand-holding-heart';
                $activities[] = [
                    'id' => 'don_' . (int)$row['DonationID'],
                    'type' => 'donation',
                    'action' => 'added',
                    'title' => 'Donation: ' . ($row['DonorName'] ?? 'Anonymous'),
                    'description' => ($row['DonationType'] ?? 'General') . ($row['Amount'] ? ' - R' . $row['Amount'] : '') . ' on ' . date('M j, Y', strtotime($row['DonationDate'])),
                    'user' => $row['recorded_by'] ?? 'system',
                    'entity_id' => (int)$row['DonationID'],
                    'entity_type' => 'donation',
                    'icon' => $icon,
                    'created_at' => date('c', strtotime($row['DonationDate']))
                ];
            }
        } catch (Exception $e) { /* donations table may not exist */ }

        // --- RECENT INVENTORY UPDATES ---
        try {
            $stmt = $db->query("
                SELECT FoodStockID as StockID, ItemName, Quantity, UpdatedAt as LastUpdated
                FROM foodstock
                WHERE UpdatedAt >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY UpdatedAt DESC
                LIMIT 10
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = [
                    'id' => 'stk_' . (int)$row['StockID'],
                    'type' => 'stock',
                    'action' => 'updated',
                    'title' => 'Stock updated: ' . $row['ItemName'],
                    'description' => 'Quantity set to ' . $row['Quantity'] . ' units on ' . date('M j, Y', strtotime($row['LastUpdated'])),
                    'user' => 'system',
                    'entity_id' => (int)$row['StockID'],
                    'entity_type' => 'stock',
                    'icon' => 'fa-boxes-stacked',
                    'created_at' => date('c', strtotime($row['LastUpdated']))
                ];
            }
        } catch (Exception $e) { /* stock table may not have LastUpdated */ }

        // --- SYSTEM EVENTS (ActivityLog) ---
        try {
            $stmt = $db->query("
                SELECT ActivityID, Action, AffectedEntityName, AffectedEntityID, Timestamp,
                       u.Username
                FROM activitylog al
                LEFT JOIN users u ON al.UserID = u.UserID
                WHERE Timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY Timestamp DESC
                LIMIT 10
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = [
                    'id' => 'act_' . (int)$row['ActivityID'],
                    'type' => strtolower($row['AffectedEntityName'] ?? 'system'),
                    'action' => strtolower($row['Action'] ?? 'updated'),
                    'title' => ucfirst($row['Action'] ?? 'Update') . ' ' . ucfirst($row['AffectedEntityName'] ?? 'Record'),
                    'description' => 'Performed by ' . ($row['Username'] ?? 'unknown') . ' on ' . date('M j, Y', strtotime($row['Timestamp'])),
                    'user' => $row['Username'] ?? 'system',
                    'entity_id' => (int)$row['AffectedEntityID'],
                    'entity_type' => strtolower($row['AffectedEntityName'] ?? 'system'),
                    'icon' => 'fa-gear',
                    'created_at' => date('c', strtotime($row['Timestamp']))
                ];
            }
        } catch (Exception $e) { /* ActivityLog error */ }

        // Sort by created_at descending
        usort($activities, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        // Keep top 20 activities
        $activities = array_slice($activities, 0, 20);
    }

    logMessage("Dashboard activity fetched by user '{$user['username']}' - " . count($activities) . " activities", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => $activities,
        'meta' => [
            'count' => count($activities),
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Dashboard activity error: " . $e->getMessage(), 'ERROR');
}