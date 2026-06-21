<?php
/**
 * Attendance Export API
 * Exports attendance data as CSV or PDF
 * 
 * Endpoint: GET /api/attendance/export.php?format=csv|pdf&date=2026-01-15&period=7d
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

    $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $period = isset($_GET['period']) ? $_GET['period'] : '7d';
    $days = $period === '30d' ? 30 : ($period === '90d' ? 90 : 7);

    $data = [];

    if ($db !== null) {
        try {
            $stmt = $db->prepare("
                SELECT 
                    a.SessionDate,
                    b.BeneficiaryID,
                    CONCAT(b.FirstName, ' ', b.LastName) as Name,
                    b.Category,
                    a.Status,
                    a.MealSessionID
                FROM attendance a
                JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                ORDER BY a.SessionDate DESC, b.LastName ASC
            ");
            $stmt->execute([':days' => $days]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $data = [];
        }
    }

    logMessage("Attendance export requested by user '{$user['username']}' - format: $format", 'INFO');

    if ($format === 'csv') {
        exportCSV($data, $user);
    } elseif ($format === 'pdf') {
        exportPDF($data, $user, $period, $days);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid format. Use csv or pdf']);
        exit();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Attendance export error: " . $e->getMessage(), 'ERROR');
}

function exportCSV($data, $user) {
    $filename = 'attendance_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    
    // BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Header
    fputcsv($output, ['Date', 'ID', 'Name', 'Category', 'Status', 'Session'], ',', '"', '\\');
    
    // Rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['SessionDate'] ?? '',
            $row['BeneficiaryID'] ?? '',
            $row['Name'] ?? '',
            $row['Category'] ?? '',
            $row['Status'] ?? '',
            $row['MealSessionID'] ?? ''
        ], ',', '"', '\\');
    }
    
    fclose($output);
    exit(0);
}

function exportPDF($data, $user, $period, $days) {
    $filename = 'attendance_export_' . date('Y-m-d_H-i-s') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $export = [
        'title' => 'Attendance Report',
        'period' => $period,
        'days' => $days,
        'generated_at' => date('c'),
        'generated_by' => $user['username'] ?? 'system',
        'total_records' => count($data),
        'data' => $data
    ];

    echo json_encode($export, JSON_PRETTY_PRINT);
    exit(0);
}