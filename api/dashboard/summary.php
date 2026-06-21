<?php
/**
 * Dashboard Summary API Endpoint
 * Returns KPI data for the mobile dashboard
 * 
 * Endpoint: GET /api/dashboard/summary.php
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "total_beneficiaries": 148,
 *     "beneficiary_meta": "+12 this month",
 *     "meals_today": 132,
 *     "meals_meta": "As of ...",
 *     "low_stock": 3,
 *     "stock_meta": "Needs attention",
 *     "active_volunteers": 12,
 *     "volunteer_meta": "8 scheduled today"
 *   }
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

    $data = [
        'total_beneficiaries' => 0,
        'beneficiary_meta' => 'No data',
        'meals_today' => 0,
        'meals_meta' => 'No data',
        'low_stock' => 0,
        'stock_meta' => 'No data',
        'active_volunteers' => 0,
        'volunteer_meta' => 'No data'
    ];

    if ($db !== null) {
        // Total active beneficiaries
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM beneficiaries WHERE Status = 'active'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['total_beneficiaries'] = (int)$row['cnt'];

        // New this month
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM beneficiaries WHERE MONTH(CreatedAt) = MONTH(CURDATE()) AND YEAR(CreatedAt) = YEAR(CURDATE())");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['beneficiary_meta'] = '+' . (int)$row['cnt'] . ' this month';

        // Meals today (attendance today)
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM attendance WHERE SessionDate = CURDATE() AND Status = 'present'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['meals_today'] = (int)$row['cnt'];
        $data['meals_meta'] = 'As of ' . date('g:i A');

        // Low stock items (< 25% or stock level)
        // Using foodstock with Quantity threshold
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE Quantity <= 25 AND Quantity > 0");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['low_stock'] = (int)$row['cnt'];
        $data['stock_meta'] = $data['low_stock'] > 0 ? 'Needs attention' : 'Stock healthy';

        // Active volunteers (approved + available)
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM volunteers WHERE Status = 'approved' AND AvailabilityStatus = 'available'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['active_volunteers'] = (int)$row['cnt'];

        // Scheduled today
        $stmt = $db->query("SELECT COUNT(DISTINCT VolunteerID) as cnt FROM volunteerschedules WHERE ScheduleDate = CURDATE() AND Status = 'scheduled'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $scheduledToday = (int)$row['cnt'];
        $data['volunteer_meta'] = $scheduledToday . ' scheduled today';

        // Total stock items
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE Quantity > 0");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalStockItems = (int)$row['cnt'];

        // Expiring within 7 days
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM foodstock WHERE ExpiryDate IS NOT NULL AND ExpiryDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND ExpiryDate >= CURDATE()");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $expiringSoon = (int)$row['cnt'];

        // Attendance rate today
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM attendance WHERE SessionDate = CURDATE()");
        $totalToday = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        $attendanceRate = $totalToday > 0 ? round(($data['meals_today'] / $totalToday) * 100) : 0;

        // Donations this month
        $stmt = $db->query("SELECT COALESCE(SUM(Amount), 0) as total, COUNT(DISTINCT DonorName) as donors FROM donations WHERE MONTH(DonationDate) = MONTH(CURDATE()) AND YEAR(DonationDate) = YEAR(CURDATE()) AND Status = 'completed'");
        $donRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mobile KPI aliases (dashboard.html data-kpi keys)
        $data['served_today'] = $data['meals_today'];
        $data['served_meta'] = $data['meals_meta'];
        $data['attendance_today'] = $totalToday;
        $data['attendance_rate'] = $attendanceRate;
        $data['volunteers_today'] = $scheduledToday;
        $data['volunteers_scheduled'] = $scheduledToday . ' scheduled';
        $data['total_stock_items'] = $totalStockItems;
        $data['low_stock_count'] = $data['low_stock'];
        $data['expiring_soon'] = $expiringSoon;
        $data['stock_value'] = 0;
        $data['donations_month'] = (float)($donRow['total'] ?? 0);
        $data['donors_count'] = (int)($donRow['donors'] ?? 0);
    }

    logMessage("Dashboard summary fetched by user '{$user['username']}'", 'INFO');
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Dashboard summary error: " . $e->getMessage(), 'ERROR');
}