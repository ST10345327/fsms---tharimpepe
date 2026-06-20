<?php
/**
 * Reports Export API Endpoint
 * Generates and returns report data in CSV format
 * 
 * Endpoint: GET /api/reports/export.php?type=beneficiaries|attendance|stock|volunteers|donations&format=csv
 * Auth: Bearer token required
 * Output: CSV file download
 */

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

    if (!in_array($type, ['beneficiaries', 'attendance', 'stock', 'volunteers', 'donations'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
    }

    $rows = [];
    $headers = [];

    if ($db !== null) {
        switch ($type) {
            case 'beneficiaries':
                $headers = ['BeneficiaryID', 'FirstName', 'LastName', 'Age', 'Gender', 'Phone', 'Status', 'RegistrationDate', 'Category'];
                $stmt = $db->query(
                    "SELECT BeneficiaryID, FirstName, LastName, Age, Gender, Phone, Status, RegistrationDate,
                            CASE WHEN Age < 18 THEN 'Child' WHEN Age >= 60 THEN 'Elderly' ELSE 'Adult' END AS Category
                     FROM Beneficiaries ORDER BY LastName, FirstName"
                );
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                break;

            case 'attendance':
                $headers = ['Date', 'Registered', 'Present', 'Absent', 'Rate (%)'];
                $stmt = $db->query(
                    "SELECT SessionDate,
                            COUNT(*) AS registered,
                            SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) AS present,
                            SUM(CASE WHEN Status = 'absent' THEN 1 ELSE 0 END) AS absent,
                            ROUND(SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) / COUNT(*) * 100) AS rate
                     FROM Attendance
                     WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                     GROUP BY SessionDate ORDER BY SessionDate DESC"
                );
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                break;

            case 'stock':
                $headers = ['ItemName', 'Quantity', 'Unit', 'ExpiryDate', 'Distributed', 'Status'];
                $stmt = $db->query(
                    "SELECT fs.ItemName, fs.Quantity, fs.Unit, fs.ExpiryDate,
                            COALESCE(SUM(fd.QuantityDistributed), 0) AS distributed,
                            CASE WHEN fs.Quantity <= 25 THEN 'Low' WHEN fs.Quantity <= 50 THEN 'Medium' ELSE 'Good' END AS status
                     FROM FoodStock fs
                     LEFT JOIN FoodDistribution fd ON fs.FoodStockID = fd.FoodStockID
                     GROUP BY fs.FoodStockID ORDER BY fs.ItemName"
                );
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                break;

            case 'volunteers':
                $headers = ['FullName', 'Phone', 'Email', 'Skills', 'Availability', 'Status', 'Total Shifts', 'Total Hours'];
                $stmt = $db->query(
                    "SELECT u.FullName, u.Phone, u.Email, v.Skills, v.AvailabilityStatus, v.Status,
                            COUNT(vs.ScheduleID) AS total_shifts,
                            COALESCE(SUM(vs.HoursWorked), 0) AS total_hours
                     FROM Volunteers v
                     JOIN Users u ON v.UserID = u.UserID
                     LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
                     GROUP BY v.VolunteerID ORDER BY u.FullName"
                );
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                break;

            case 'donations':
                $headers = ['DonorName', 'Type', 'Description', 'Amount', 'Date', 'Status', 'PaymentMethod'];
                $stmt = $db->query(
                    "SELECT DonorName, DonationType, Description, Amount, DonationDate, Status, COALESCE(PaymentMethod, 'N/A') AS PaymentMethod
                     FROM Donations
                     WHERE DonationDate >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)
                     ORDER BY DonationDate DESC"
                );
                $rows = $stmt->fetchAll(PDO::FETCH_NUM);
                break;
        }
    }

    // Generate CSV
    $filename = "fsms_{$type}_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Headers
    fputcsv($output, $headers);

    // Data rows
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }

    fclose($output);

    logMessage("Report exported: {$type} CSV by user '{$user['username']}'", 'INFO');
    exit();

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Reports export error: " . $e->getMessage(), 'ERROR');
}