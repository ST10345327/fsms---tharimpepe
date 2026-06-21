<?php
/**
 * Reports Generate API Endpoint
 * Generates a report of the specified type and returns data/URL
 * 
 * Endpoint: GET /api/reports/generate.php?type=beneficiaries|attendance|stock|volunteers|donations
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

    $type = $_GET['type'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';

    if (!in_array($type, ['beneficiaries', 'attendance', 'stock', 'volunteers', 'donations', 'financial'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
    }

    $data = [];
    $reportName = '';
    $summary = [];

    if ($db !== null) {
        switch ($type) {
            case 'beneficiaries':
                $reportName = 'Beneficiary Report';
                $sql = "
                    SELECT BeneficiaryID, CONCAT(FirstName, ' ', LastName) AS FullName, 
                            Age, Gender, Phone, RegistrationDate, Status, Category,
                            CASE WHEN Age < 18 THEN 'Child' WHEN Age >= 60 THEN 'Elderly' ELSE 'Adult' END AS age_group
                     FROM beneficiaries
                ";
                $params = [];
                if ($from) { $sql .= " WHERE RegistrationDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " RegistrationDate <= :to"; $params[':to'] = $to; }
                $sql .= " ORDER BY LastName, FirstName";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $summary = [
                    'total' => count($data),
                    'active' => 0,
                    'inactive' => 0,
                    'suspended' => 0,
                    'by_status' => [],
                    'by_age_group' => []
                ];
                foreach ($data as $row) {
                    $status = strtolower($row['Status'] ?? 'unknown');
                    $summary['by_status'][$status] = ($summary['by_status'][$status] ?? 0) + 1;
                    if ($status === 'active') {
                        $summary['active']++;
                    } elseif ($status === 'inactive') {
                        $summary['inactive']++;
                    } elseif ($status === 'suspended') {
                        $summary['suspended']++;
                    }
                    $ageGroup = $row['age_group'] ?? 'Unknown';
                    $summary['by_age_group'][$ageGroup] = ($summary['by_age_group'][$ageGroup] ?? 0) + 1;
                }
                break;

            case 'attendance':
                $reportName = 'Attendance Report';
                $sql = "
                    SELECT a.SessionDate AS date, COUNT(*) AS registered,
                            SUM(CASE WHEN LOWER(a.Status) = 'present' THEN 1 ELSE 0 END) AS present,
                            SUM(CASE WHEN LOWER(a.Status) = 'absent' THEN 1 ELSE 0 END) AS absent,
                            SUM(CASE WHEN LOWER(a.Status) = 'late' THEN 1 ELSE 0 END) AS late,
                            ROUND(SUM(CASE WHEN LOWER(a.Status) IN ('present', 'late') THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) AS rate
                     FROM attendance a
                     WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ";
                $params = [];
                if ($from) { $sql .= " AND a.SessionDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= " AND a.SessionDate <= :to"; $params[':to'] = $to; }
                $sql .= " GROUP BY a.SessionDate ORDER BY a.SessionDate DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $summary = [
                    'total_days' => count($data),
                    'total_sessions' => count($data),
                    'total_present' => array_sum(array_column($data, 'present')),
                    'total_absent' => array_sum(array_column($data, 'absent')),
                    'avg_attendance' => 0,
                    'avg_present' => 0,
                    'avg_rate' => '0%'
                ];
                if (!empty($data)) {
                    $summary['avg_present'] = round(array_sum(array_column($data, 'present')) / count($data), 1);
                    $rates = array_map(fn($d) => (float)$d['rate'], $data);
                    $summary['avg_attendance'] = round(array_sum($rates) / count($rates), 1);
                    $summary['avg_rate'] = $summary['avg_attendance'] . '%';
                }
                break;

            case 'stock':
                $reportName = 'Stock Report';
                $stmt = $db->query(
                    "SELECT fs.ItemName, fs.Quantity, fs.Unit, fs.ExpiryDate, fs.QuantityRemaining as MinStockLevel,
                            COALESCE(SUM(fd.QuantityDistributed), 0) AS distributed,
                            CASE 
                                WHEN fs.Quantity = 0 THEN 'Out of Stock'
                                WHEN fs.Quantity <= 10 THEN 'Low Stock'
                                WHEN fs.Quantity <= 20 THEN 'Medium'
                                ELSE 'Good'
                            END AS stock_status
                     FROM foodstock fs
                     LEFT JOIN FoodDistribution fd ON fs.FoodStockID = fd.FoodStockID
                     GROUP BY fs.FoodStockID ORDER BY fs.ItemName"
                );
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $summary = [
                    'total_items' => count($data),
                    'low_stock' => 0,
                    'out_of_stock' => 0,
                    'expiring_soon' => 0
                ];
                foreach ($data as $row) {
                    if ($row['stock_status'] === 'Out of Stock') $summary['out_of_stock']++;
                    elseif ($row['stock_status'] === 'Low Stock') $summary['low_stock']++;
                    if ($row['ExpiryDate'] && strtotime($row['ExpiryDate']) < strtotime('+7 days')) {
                        $summary['expiring_soon']++;
                    }
                }
                break;

            case 'volunteers':
                $reportName = 'Volunteer Report';
                $sql = "
                    SELECT u.FullName, u.Phone, u.Email, v.Skills, v.AvailabilityStatus, v.Status,
                            COUNT(vs.ScheduleID) AS total_shifts,
                            COALESCE(SUM(vs.HoursWorked), 0) AS total_hours,
                            COUNT(CASE WHEN vs.Status = 'completed' THEN 1 END) AS completed_shifts
                     FROM volunteers v
                     JOIN users u ON v.UserID = u.UserID
                     LEFT JOIN volunteerschedules vs ON v.VolunteerID = vs.VolunteerID
                ";
                $params = [];
                if ($from) { $sql .= " WHERE vs.ScheduleDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " vs.ScheduleDate <= :to"; $params[':to'] = $to; }
                $sql .= " GROUP BY v.VolunteerID ORDER BY u.FullName";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $summary = [
                    'total' => count($data),
                    'active' => 0,
                    'total_hours' => 0,
                    'avg_hours' => 0
                ];
                foreach ($data as $row) {
                    if ($row['Status'] === 'approved') $summary['active']++;
                    $summary['total_hours'] += (float)$row['total_hours'];
                }
                if ($summary['total'] > 0) {
                    $summary['avg_hours'] = round($summary['total_hours'] / $summary['total'], 1);
                }
                break;

            case 'financial':
                $reportName = 'Financial Summary';
                $sql = "
                    SELECT DonationType AS type,
                           COUNT(*) AS count,
                           COALESCE(SUM(Amount), 0) AS total_amount
                    FROM donations
                ";
                $params = [];
                if ($from) {
                    $sql .= " WHERE DonationDate >= :from";
                    $params[':from'] = $from;
                }
                if ($to) {
                    $sql .= ($from ? " AND" : " WHERE") . " DonationDate <= :to";
                    $params[':to'] = $to;
                }
                $sql .= " GROUP BY DonationType ORDER BY total_amount DESC";

                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $totalValue = array_sum(array_column($data, 'total_amount'));
                $totalCount = array_sum(array_column($data, 'count'));
                $summary = [
                    'total_donations' => $totalCount,
                    'total_value' => $totalValue,
                    'unique_donors' => 0,
                    'avg_donation' => $totalCount > 0 ? round($totalValue / $totalCount, 2) : 0
                ];

                try {
                    $donorStmt = $db->query("SELECT COUNT(DISTINCT DonorName) AS cnt FROM donations");
                    $summary['unique_donors'] = (int)$donorStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
                } catch (Exception $e) {
                    $summary['unique_donors'] = 0;
                }
                break;

            case 'donations':
                $reportName = 'Donations Report';
                $sql = "
                    SELECT DonorName, DonationType AS type, 
                            CASE WHEN DonationType = 'cash' THEN CONCAT('R', FORMAT(Amount, 2)) ELSE Description END AS item,
                            DonationDate AS date, Status, Amount
                     FROM donations
                ";
                $params = [];
                if ($from) { $sql .= " WHERE DonationDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " DonationDate <= :to"; $params[':to'] = $to; }
                $sql .= " ORDER BY DonationDate DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $totalAmount = array_sum(array_column($data, 'Amount'));
                $summary = [
                    'total_donations' => count($data),
                    'total_value' => $totalAmount,
                    'total_amount' => $totalAmount,
                    'unique_donors' => count(array_unique(array_column($data, 'DonorName'))),
                    'avg_donation' => count($data) > 0 ? round($totalAmount / count($data), 2) : 0
                ];
                break;
        }
    }

    require_once __DIR__ . '/../../app/models/ActivityLog.php';
    $userId = $user['user_id'] ?? $user['id'] ?? 0;
    ActivityLog::log($userId, 'generate_report', 'Report', $type, $reportName ?: ucfirst($type) . ' Report');

    logMessage("Report '{$reportName}' generated by user '{$user['username']}'", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => "{$reportName} generated",
        'data' => $data,
        'summary' => $summary,
        'type' => $type,
        'count' => count($data),
        'generated_at' => date('c'),
        'generated_by' => $user['username'] ?? 'system'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Reports generate error: " . $e->getMessage(), 'ERROR');
}