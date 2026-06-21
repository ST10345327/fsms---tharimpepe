<?php
/**
 * Reports Export API
 * Export reports in multiple formats (CSV, JSON, PDF-ready)
 * 
 * Endpoint: GET /api/reports/export.php?type=beneficiaries|attendance|stock|volunteers|donations&format=csv|json&from=2026-01-01&to=2026-01-31
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
    $format = strtolower($_GET['format'] ?? 'json');
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';

    $validTypes = ['beneficiaries', 'attendance', 'stock', 'volunteers', 'donations', 'financial'];
    if (!in_array($type, $validTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
    }

    // Generate report data using the same logic as generate.php
    $data = [];
    $reportName = '';
    $summary = [];

    if ($db !== null) {
        try {
            // Build report data inline
            generateReportData($db, $type, $from, $to, $data, $reportName, $summary);
        } catch (Exception $e) {
            $data = [];
        }
    }

    logMessage("Report export requested: $reportName in $format by user '{$user['username']}'", 'INFO');

    require_once __DIR__ . '/../../app/models/ActivityLog.php';
    $userId = $user['user_id'] ?? $user['id'] ?? 0;
    ActivityLog::log($userId, 'export_report', 'Report', $type, $reportName ?: ucfirst($type) . ' Report');

    if ($format === 'csv') {
        exportReportCSV($data, $reportName, $user);
    } elseif ($format === 'pdf') {
        exportReportPDF($data, $reportName, $summary, $user);
    } else {
        // Default JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'summary' => $summary,
            'type' => $type,
            'count' => count($data)
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Report export error: " . $e->getMessage(), 'ERROR');
}

function generateReportData($db, $type, $from, $to, &$data, &$reportName, &$summary) {
    $reportName = ucfirst($type) . ' Report';

    try {
        switch ($type) {
            case 'beneficiaries':
                $sql = "SELECT BeneficiaryID, CONCAT(FirstName, ' ', LastName) AS FullName, Age, Gender, Phone, RegistrationDate, Status, Category FROM beneficiaries";
                $params = [];
                if ($from) { $sql .= " WHERE RegistrationDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " RegistrationDate <= :to"; $params[':to'] = $to; }
                $sql .= " ORDER BY LastName, FirstName";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'attendance':
                $sql = "SELECT a.SessionDate, COUNT(*) as total, SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) as present FROM attendance a WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $params = [];
                if ($from) { $sql .= " AND a.SessionDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= " AND a.SessionDate <= :to"; $params[':to'] = $to; }
                $sql .= " GROUP BY a.SessionDate ORDER BY a.SessionDate DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'volunteers':
                $sql = "SELECT u.FullName, u.Phone, u.Email, v.Skills, v.AvailabilityStatus, v.Status, COUNT(vs.ScheduleID) AS total_shifts, COALESCE(SUM(vs.HoursWorked), 0) AS total_hours FROM volunteers v JOIN users u ON v.UserID = u.UserID LEFT JOIN volunteerschedules vs ON v.VolunteerID = vs.VolunteerID";
                $params = [];
                if ($from) { $sql .= " WHERE vs.ScheduleDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " vs.ScheduleDate <= :to"; $params[':to'] = $to; }
                $sql .= " GROUP BY v.VolunteerID ORDER BY u.FullName";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'donations':
                $sql = "SELECT DonorName, DonationType AS type, DonationDate AS date, Quantity, Unit, Amount FROM donations";
                $params = [];
                if ($from) { $sql .= " WHERE DonationDate >= :from"; $params[':from'] = $from; }
                if ($to) { $sql .= ($from ? " AND" : " WHERE") . " DonationDate <= :to"; $params[':to'] = $to; }
                $sql .= " ORDER BY DonationDate DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'stock':
                $sql = "SELECT fs.ItemName, fs.Quantity, fs.Unit, fs.ExpiryDate, fs.QuantityRemaining as MinStockLevel FROM foodstock fs ORDER BY fs.ItemName";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            default:
                $data = [];
        }
    } catch (Exception $e) {
        $data = [];
    }
}

function exportReportCSV($data, $reportName, $user) {
    $filename = preg_replace('/[^a-z0-9_]/i', '_', strtolower($reportName)) . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    if (!empty($data)) {
        $headers = array_keys($data[0]);
        fputcsv($output, $headers);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit(0);
}

function exportReportPDF($data, $reportName, $summary, $user) {
    $filename = preg_replace('/[^a-z0-9_]/i', '_', strtolower($reportName)) . '_' . date('Y-m-d_H-i-s') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $report = [
        'report_name' => $reportName,
        'generated_at' => date('c'),
        'generated_by' => $user['username'] ?? 'system',
        'organization' => 'Tharimpepe Feeding Scheme',
        'summary' => $summary,
        'total_records' => count($data),
        'data' => $data,
        'note' => 'For PDF generation, use a client-side PDF library'
    ];

    echo json_encode($report, JSON_PRETTY_PRINT);
    exit(0);
}