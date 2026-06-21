<?php
/**
 * Beneficiary Detail API
 * Returns full details for a single beneficiary including attendance history
 * 
 * Endpoint: GET /api/beneficiaries/detail.php?id=123
 * Auth: Bearer token required
 * Output: {
 *   "success": true,
 *   "data": {
 *     "BeneficiaryID": 1,
 *     "FullName": "John Doe",
 *     "Status": "Active",
 *     "Gender": "Male",
 *     "DateOfBirth": "2010-05-15",
 *     "Age": 14,
 *     "GuardianName": "Jane Doe",
 *     "ContactNumber": "0712345678",
 *     "Address": "123 Main St",
 *     "Area": "Soweto",
 *     "RegistrationDate": "2024-01-10",
 *     "Notes": "Allergic to peanuts",
 *     "attendance_history": [
 *       {"date": "2026-01-14", "status": "present", "session": "lunch"},
 *       {"date": "2026-01-13", "status": "absent", "session": "lunch"}
 *     ],
 *     "attendance_summary": {
 *       "total_sessions": 20,
 *       "present": 18,
 *       "absent": 2,
 *       "rate": "90%"
 *     }
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

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Beneficiary ID is required']);
        exit();
    }

    if ($db === null) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Database not available']);
        exit();
    }

    // Fetch beneficiary
    $stmt = $db->prepare("SELECT * FROM beneficiaries WHERE BeneficiaryID = :id");
    $stmt->execute([':id' => $id]);
    $beneficiary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$beneficiary) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Beneficiary not found']);
        exit();
    }

    // Calculate age
    $dob = $beneficiary['DateOfBirth'] ?? null;
    $age = null;
    if ($dob) {
        try {
            $birth = new DateTime($dob);
            $now = new DateTime();
            $age = $now->diff($birth)->y;
        } catch (Exception $e) { /* ignore */ }
    }

    // Build response
    $data = [
        'BeneficiaryID' => (int)$beneficiary['BeneficiaryID'],
        'FullName' => trim(($beneficiary['FirstName'] ?? '') . ' ' . ($beneficiary['LastName'] ?? '')),
        'FirstName' => $beneficiary['FirstName'] ?? '',
        'LastName' => $beneficiary['LastName'] ?? '',
        'Status' => ucfirst($beneficiary['Status'] ?? 'Unknown'),
        'Gender' => $beneficiary['Gender'] ?? null,
        'DateOfBirth' => $beneficiary['DateOfBirth'] ?? null,
        'Age' => $age,
        'GuardianName' => $beneficiary['GuardianName'] ?? $beneficiary['guardian_name'] ?? null,
        'ContactNumber' => $beneficiary['ContactNumber'] ?? $beneficiary['Phone'] ?? $beneficiary['phone'] ?? null,
        'Address' => $beneficiary['Address'] ?? $beneficiary['address'] ?? null,
        'Area' => $beneficiary['Area'] ?? $beneficiary['area'] ?? null,
        'RegistrationDate' => $beneficiary['RegistrationDate'] ?? $beneficiary['CreatedAt'] ?? null,
        'Notes' => $beneficiary['Notes'] ?? $beneficiary['notes'] ?? null,
        'Category' => $beneficiary['Category'] ?? $beneficiary['category'] ?? null,
        'CreatedAt' => $beneficiary['CreatedAt'] ?? null
    ];

    // Fetch attendance history (last 30 days)
    $attendanceHistory = [];
    $attendanceSummary = [
        'total_sessions' => 0,
        'present' => 0,
        'absent' => 0,
        'late' => 0,
        'excused' => 0,
        'rate' => '0%'
    ];

    try {
        $stmt = $db->prepare("
            SELECT a.SessionDate, a.Status, a.MealSessionID, ms.SessionType as SessionName
            FROM Attendance a
            LEFT JOIN MealSession ms ON a.MealSessionID = ms.MealSessionID
            WHERE a.BeneficiaryID = :id
              AND a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY a.SessionDate DESC, a.CreatedAt DESC
            LIMIT 50
        ");
        $stmt->execute([':id' => $id]);
        $attendanceHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attendanceSummary['total_sessions'] = count($attendanceHistory);
        foreach ($attendanceHistory as $att) {
            $status = strtolower($att['Status'] ?? 'present');
            $valid = ['present', 'absent', 'late', 'excused'];
            if (!in_array($status, $valid, true)) $status = 'present';
            if (isset($attendanceSummary[$status])) {
                $attendanceSummary[$status]++;
            }
        }

        if ($attendanceSummary['total_sessions'] > 0) {
            $rate = ($attendanceSummary['present'] / $attendanceSummary['total_sessions']) * 100;
            $attendanceSummary['rate'] = round($rate) . '%';
        }
    } catch (Exception $e) { /* attendance tables may not be set up */ }

    $data['attendance_history'] = $attendanceHistory;
    $data['attendance_summary'] = $attendanceSummary;

    logMessage("Beneficiary detail fetched by user '{$user['username']}' - ID $id", 'INFO');
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Beneficiary detail error: " . $e->getMessage(), 'ERROR');
}