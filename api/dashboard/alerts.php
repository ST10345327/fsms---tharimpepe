<?php
/**
 * Dashboard Alerts API Endpoint
 * Returns real-time alerts for low stock, expiry, pending registrations, etc.
 * 
 * Endpoint: GET /api/dashboard/alerts.php
 * Auth: Bearer token required
 * Output format:
 *   {
 *     "success": true,
 *     "data": [
 *     {
 *       "id": "alert_unique_id",
 *       "type": "low_stock|expiry|pending_registration|...",
 *       "severity": "critical|warning|info",
 *       "title": "Short description",
 *       "description": "Longer description with details",
 *       "entity_id": 123,
 *       "entity_type": "stock|beneficiary|donation|volunteer",
 *       "action_url": "/stock.html?id=123",
 *       "action_label": "View Item",
 *       "created_at": "2026-01-15T10:30:00Z",
 *       "icon": "font-awesome-icon-class"
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
    $alerts = [];

    if ($db !== null) {
        // --- LOW STOCK ALERTS ---
        $stmt = $db->query("
            SELECT FoodStockID as StockID, ItemName, Quantity
            FROM foodstock
            WHERE Quantity <= 10 AND Quantity > 0
            ORDER BY Quantity ASC 
            LIMIT 5
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $minLevel = 10;
            $shortfall = max(0, $minLevel - $row['Quantity']);
            $alerts[] = [
                'id' => 'stock_low_' . $row['StockID'],
                'type' => 'low_stock',
                'severity' => $shortfall > 5 ? 'critical' : 'warning',
                'title' => 'Low stock: ' . $row['ItemName'],
                'description' => 'Only ' . $row['Quantity'] . ' units remaining (min: ' . $minLevel . '). Need ' . $shortfall . ' more.',
                'entity_id' => (int)$row['StockID'],
                'entity_type' => 'stock',
                'action_url' => 'stock.html',
                'action_label' => 'View Stock',
                'icon' => 'fa-boxes-stacked',
                'created_at' => date('c')
            ];
        }

        // --- EXPIRY ALERTS (7, 14, 30 days) ---
        $stmt = $db->query("
            SELECT FoodStockID as StockID, ItemName, Quantity, ExpiryDate, Unit,
                   DATEDIFF(ExpiryDate, CURDATE()) as days_left
            FROM foodstock
            WHERE ExpiryDate IS NOT NULL 
              AND DATEDIFF(ExpiryDate, CURDATE()) BETWEEN 0 AND 30
            ORDER BY days_left ASC
            LIMIT 5
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $days = (int)$row['days_left'];
            if ($days < 0) continue; // already expired
            $severity = $days <= 7 ? 'critical' : ($days <= 14 ? 'warning' : 'info');
            $suffix = $days == 1 ? 'day' : 'days';
            $alerts[] = [
                'id' => 'stock_expiry_' . $row['StockID'],
                'type' => 'expiry',
                'severity' => $severity,
                'title' => $row['ItemName'] . ' expires soon',
                'description' => $row['ItemName'] . ' (' . $row['Quantity'] . ' ' . ($row['Unit'] ?? 'units') . ') expires in ' . $days . ' ' . $suffix . ' (' . date('M j', strtotime($row['ExpiryDate'])) . ').',
                'entity_id' => (int)$row['StockID'],
                'entity_type' => 'stock',
                'action_url' => 'stock.html',
                'action_label' => 'View Stock',
                'icon' => 'fa-calendar-exclamation',
                'created_at' => date('c')
            ];
        }

        // --- PENDING BENEFICIARY REGISTRATIONS ---
        $hasRegistrationsTable = false;
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'beneficiary_registrations'");
            $hasRegistrationsTable = (bool)$stmt->fetchColumn();
        } catch (Exception $e) { /* table may not exist */ }
        if ($hasRegistrationsTable) {
            $stmt = $db->query("
                SELECT RegistrationID, FirstName, LastName, RegistrationDate
                FROM beneficiary_registrations
                WHERE Status = 'pending'
                ORDER BY RegistrationDate ASC
                LIMIT 5
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alerts[] = [
                    'id' => 'pending_reg_' . $row['RegistrationID'],
                    'type' => 'pending_registration',
                    'severity' => 'info',
                    'title' => 'New registration: ' . $row['FirstName'] . ' ' . $row['LastName'],
                    'description' => 'Awaiting approval since ' . date('M j, Y', strtotime($row['RegistrationDate'])) . '.',
                    'entity_id' => (int)$row['RegistrationID'],
                    'entity_type' => 'beneficiary',
                    'action_url' => 'beneficiaries.html',
                    'action_label' => 'Review',
                    'icon' => 'fa-user-check',
                    'created_at' => date('c')
                ];
            }
        }

        // --- UPCOMING SCHEDULED DONATIONS ---
        $hasDonationSchedules = false;
        try {
            $stmt = $db->query("SHOW TABLES LIKE 'DonationSchedules'");
            $hasDonationSchedules = (bool)$stmt->fetchColumn();
        } catch (Exception $e) { /* table may not exist */ }
        if ($hasDonationSchedules) {
            $stmt = $db->query("
                SELECT ScheduleID, DonorName, ScheduledDate, ItemType, Quantity
                FROM DonationSchedules
                WHERE Status = 'scheduled'
                  AND ScheduledDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY ScheduledDate ASC
                LIMIT 3
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alerts[] = [
                    'id' => 'scheduled_donation_' . (int)$row['ScheduleID'],
                    'type' => 'scheduled_donation',
                    'severity' => 'info',
                    'title' => 'Donation expected: ' . ($row['DonorName'] ?? 'Anonymous'),
                    'description' => ($row['ItemType'] ?? 'Donation') . ($row['Quantity'] ? ' (' . $row['Quantity'] . ' units)' : '') . ' scheduled for ' . date('M j', strtotime($row['ScheduledDate'])) . '.',
                    'entity_id' => (int)$row['ScheduleID'],
                    'entity_type' => 'donation',
                    'action_url' => 'stock.html',
                    'action_label' => 'View Donations',
                    'icon' => 'fa-hand-holding-heart',
                    'created_at' => date('c')
                ];
            }
        }

        // --- VOLUNTEERS WITHOUT SCHEDULE THIS WEEK ---
        $stmt = $db->query("
            SELECT v.VolunteerID, u.FullName as name
            FROM volunteers v
            JOIN users u ON v.UserID = u.UserID
            LEFT JOIN volunteerschedules vs
                ON v.VolunteerID = vs.VolunteerID 
                AND vs.ScheduleDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            WHERE v.Status = 'approved'
            GROUP BY v.VolunteerID, u.FullName
            HAVING COUNT(vs.ScheduleID) = 0
            LIMIT 3
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $name = $row['name'] ?: 'Volunteer #' . $row['VolunteerID'];
            $alerts[] = [
                'id' => 'vol_no_sched_' . $row['VolunteerID'],
                'type' => 'volunteer_unscheduled',
                'severity' => 'info',
                'title' => $name . ' has no upcoming shifts',
                'description' => 'Approved volunteer has no shifts scheduled for the coming week.',
                'entity_id' => (int)$row['VolunteerID'],
                'entity_type' => 'volunteer',
                'action_url' => 'volunteers.html',
                'action_label' => 'View Volunteer',
                'icon' => 'fa-user-clock',
                'created_at' => date('c')
            ];
        }

        // --- HIGH ABSENTEEISM FLAG (if attendance table has data) ---
        try {
            $stmt = $db->query("
                SELECT COUNT(*) as total_present,
                       SUM(CASE WHEN Status = 'absent' THEN 1 ELSE 0 END) as total_absent
                FROM attendance
                WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ");
            $att = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)($att['total_present'] ?? 0) + (int)($att['total_absent'] ?? 0);
            $absent = (int)($att['total_absent'] ?? 0);
            if ($total > 0 && ($absent / $total) > 0.2) {
                $alerts[] = [
                    'id' => 'high_absenteeism',
                    'type' => 'attendance_warning',
                    'severity' => 'warning',
                    'title' => 'High absenteeism rate',
                    'description' => 'Absenteeism is at ' . round(($absent / $total) * 100, 1) . '% this week. Review attendance patterns.',
                    'entity_id' => 0,
                    'entity_type' => 'system',
                    'action_url' => 'reports.html',
                    'action_label' => 'View Report',
                    'icon' => 'fa-chart-line',
                    'created_at' => date('c')
                ];
            }
        } catch (Exception $e) { /* attendance may not have data */ }

        // Sort by severity (critical first, then warning, then info)
        $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
        usort($alerts, function ($a, $b) use ($severityOrder) {
            return ($severityOrder[$a['severity']] ?? 99) <=> ($severityOrder[$b['severity']] ?? 99);
        });
    }

    logMessage("Dashboard alerts fetched by user '{$user['username']}' - " . count($alerts) . " alerts", 'INFO');
    echo json_encode([
        'success' => true,
        'data' => array_values($alerts),
        'meta' => [
            'count' => count($alerts),
            'timestamp' => date('c')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Dashboard alerts error: " . $e->getMessage(), 'ERROR');
}