<?php
/**
 * Module: Attendance Tracking Controller
 * Purpose: Web interface for attendance management and meal distribution tracking
 * Reference: Task 2b System Design Section 4.4 - Attendance Management
 * Author: WIL Student
 * Controller: AttendanceController
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../helpers/bootstrap.php";

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../helpers/Rbac.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../models/Attendance.php";
require_once __DIR__ . "/../models/Beneficiary.php";
require_once __DIR__ . "/../models/ActivityLog.php";

requireLogin();

// HZ-ATT-CTRL-RBAC: Enforce attendance permission (admin, staff, volunteer)
rbacRequirePermission('attendance');

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
generateCSRFToken();

// Initialize models
$attendanceModel = new Attendance($db);
$beneficiaryModel = new Beneficiary($db);

// Get action from URL parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle different actions
/**
 * HZ-ATT-CTRL-001
 * Purpose: Display attendance list with filtering and search
 * Flow: Get parameters -> Fetch records -> Display list
 */
if ($action === 'list') {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $dateFilter = $_GET['date'] ?? null;
    $statusFilter = $_GET['status'] ?? null;
    $beneficiaryId = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : null;
    $searchTerm = trim($_GET['search'] ?? '');

    $attendance = $attendanceModel->getAllAttendance($limit, $offset, $dateFilter, $statusFilter, $beneficiaryId, $searchTerm);
    $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0);

    // Get statistics if date range is provided
    $stats = null;
    if ($dateFilter) {
        $stats = $attendanceModel->getAttendanceStats($dateFilter, $dateFilter);
    }

    include __DIR__ . "/../views/attendance/list.php";
}

/**
 * HZ-ATT-CTRL-002
 * Purpose: Display form to record new attendance
 * Flow: Show form with beneficiary selection
 */
if ($action === 'create') {
    // Get active beneficiaries for selection
    $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');
    $recentAttendance = $attendanceModel->getRecentAttendance(5);

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        include __DIR__ . "/../views/attendance/create.php";
    }

    /**
     * HZ-ATT-CTRL-003
     * Purpose: Process attendance recording form submission
     * Flow: Validate input -> Record attendance -> Redirect
     */
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = "Invalid request token";
            include __DIR__ . "/../views/attendance/create.php";
            exit();
        }

        $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;
        $sessionDate = $_POST['session_date'] ?? "";
        $status = $_POST['status'] ?? "present";
        $notes = trim($_POST['notes'] ?? "");

        // Validation
        if (empty($beneficiaryId) || empty($sessionDate)) {
            $error = "Beneficiary and session date are required";
        } else {
            try {
                $attendanceId = $attendanceModel->recordAttendance($beneficiaryId, $sessionDate, $status, $notes);

                if ($attendanceId) {
                    ActivityLog::log(getCurrentUser()['user_id'], 'record_attendance', 'Attendance', $attendanceId, "Recorded attendance for beneficiary $beneficiaryId on $sessionDate (Status: $status)");
                    $success = "Attendance recorded successfully!";
                    header("Refresh: 2; URL=AttendanceController.php?action=view&id=" . $attendanceId);
                } else {
                    $error = "Failed to record attendance";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        include __DIR__ . "/../views/attendance/create.php";
    }
}

/**
 * HZ-ATT-CTRL-004
 * Purpose: Display attendance details
 * Flow: Get attendance record -> Show details
 */
if ($action === 'view') {
    $attendanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($attendanceId <= 0) {
        header("Location: AttendanceController.php?action=list&error=Invalid attendance ID");
        exit();
    }

    $attendance = $attendanceModel->getAttendanceById($attendanceId);

    if (!$attendance) {
        header("Location: AttendanceController.php?action=list&error=Attendance record not found");
        exit();
    }

    $history = $attendanceModel->getBeneficiaryAttendance($attendance['BeneficiaryID'], 10);
    include __DIR__ . "/../views/attendance/view.php";
}

/**
 * HZ-ATT-CTRL-005
 * Purpose: Display form to edit attendance record
 * Flow: Get record -> Show edit form
 */
if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "GET") {
    if (!hasRole('admin') && !hasRole('staff')) {
        header("Location: AttendanceController.php?action=list&error=You do not have permission to edit attendance records");
        exit();
    }

    $attendanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($attendanceId <= 0) {
        header("Location: AttendanceController.php?action=list&error=Invalid attendance ID");
        exit();
    }

    $attendance = $attendanceModel->getAttendanceById($attendanceId);
    $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');
    $recentAttendance = $attendanceModel->getRecentAttendance(5);

    if (!$attendance) {
        header("Location: AttendanceController.php?action=list&error=Attendance record not found");
        exit();
    }

    include __DIR__ . "/../views/attendance/edit.php";
}

/**
 * HZ-ATT-CTRL-006
 * Purpose: Process attendance edit form submission
 * Flow: Validate input -> Update record -> Redirect
 */
if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hasRole('admin') && !hasRole('staff')) {
        header("Location: AttendanceController.php?action=list&error=You do not have permission to edit attendance records");
        exit();
    }

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header("Location: AttendanceController.php?action=list&error=Invalid request token");
        exit();
    }

    $attendanceId = isset($_POST['attendance_id']) ? (int)$_POST['attendance_id'] : 0;
    $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;
    $sessionDate = $_POST['session_date'] ?? "";
    $status = $_POST['status'] ?? "present";
    $notes = trim($_POST['notes'] ?? "");

    if (empty($beneficiaryId) || empty($sessionDate)) {
        $error = "Beneficiary and session date are required";
    } else {
        try {
            if ($attendanceModel->updateAttendance($attendanceId, $beneficiaryId, $sessionDate, $status, $notes)) {
                ActivityLog::log(getCurrentUser()['user_id'], 'update_attendance', 'Attendance', $attendanceId, "Updated attendance record for beneficiary $beneficiaryId (Status: $status)");
                $success = "Attendance record updated successfully!";
                header("Refresh: 2; URL=AttendanceController.php?action=view&id=" . $attendanceId);
            } else {
                $error = "Failed to update attendance record";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    $attendance = $attendanceModel->getAttendanceById($attendanceId);
    $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');
    $recentAttendance = $attendanceModel->getRecentAttendance(5);
    include __DIR__ . "/../views/attendance/edit.php";
}

/**
 * HZ-ATT-CTRL-007
 * Purpose: Delete attendance record
 * Flow: Confirm deletion -> Delete record -> Redirect
 */
if ($action === 'delete') {
    if (!hasRole('admin') && !hasRole('staff')) {
        header("Location: AttendanceController.php?action=list&error=You do not have permission to delete attendance records");
        exit();
    }

    $attendanceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $csrfToken = $_GET['csrf_token'] ?? '';

    // Verify CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
        header("Location: AttendanceController.php?action=list&error=Invalid request");
        exit();
    }

    if ($attendanceId <= 0) {
        header("Location: AttendanceController.php?action=list&error=Invalid attendance ID");
        exit();
    }

    try {
        if ($attendanceModel->deleteAttendance($attendanceId)) {
            ActivityLog::log(getCurrentUser()['user_id'], 'delete_attendance', 'Attendance', $attendanceId, "Deleted attendance record");
            header("Location: AttendanceController.php?action=list&success=Attendance record deleted successfully");
        } else {
            header("Location: AttendanceController.php?action=list&error=Failed to delete attendance record");
        }
    } catch (Exception $e) {
        header("Location: AttendanceController.php?action=list&error=" . urlencode($e->getMessage()));
    }
    exit();
}

/**
 * HZ-ATT-CTRL-008
 * Purpose: Display daily attendance summary for a specific date
 * Flow: Get date -> Fetch summary -> Display
 */
if ($action === 'daily-summary') {
    $sessionDate = $_GET['date'] ?? date('Y-m-d');

    if (!strtotime($sessionDate)) {
        $sessionDate = date('Y-m-d');
    }

    $summary = $attendanceModel->getDailyAttendanceSummary($sessionDate);
    $stats = $attendanceModel->getAttendanceStats($sessionDate, $sessionDate);

    include __DIR__ . "/../views/attendance/daily_summary.php";
}

/**
 * HZ-ATT-CTRL-009
 * Purpose: Bulk record attendance for multiple beneficiaries
 * Flow: Show form -> Process bulk recording
 */
if ($action === 'bulk-record') {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $sessionDate = $_GET['date'] ?? date('Y-m-d');
        $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');

        include __DIR__ . "/../views/attendance/bulk_record.php";
    }

    /**
     * HZ-ATT-CTRL-010
     * Purpose: Process bulk attendance recording
     * Flow: Validate data -> Bulk record -> Show results
     */
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = "Invalid request token";
            $sessionDate = $_POST['session_date'] ?? date('Y-m-d');
            $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');
            include __DIR__ . "/../views/attendance/bulk_record.php";
            exit();
        }

        $sessionDate = $_POST['session_date'] ?? "";
        $attendanceData = $_POST['attendance'] ?? [];

        if (empty($sessionDate)) {
            $error = "Session date is required";
        } elseif (empty($attendanceData)) {
            $error = "No attendance data provided";
        } else {
            try {
                $results = $attendanceModel->bulkRecordAttendance($sessionDate, $attendanceData);
                $successCount = count(array_filter($results, function($r) { return $r['success']; }));
                $totalCount = count($results);

                $message = "Bulk attendance recording completed. $successCount of $totalCount records processed successfully.";
                header("Location: AttendanceController.php?action=daily-summary&date=$sessionDate&success=" . urlencode($message));
                exit();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0, 'active');
        include __DIR__ . "/../views/attendance/bulk_record.php";
    }
}

/**
 * HZ-ATT-CTRL-011
 * Purpose: Generate attendance reports
 * Flow: Get parameters -> Generate report -> Display
 */
if ($action === 'report') {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $beneficiaryId = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : null;
    $statusFilter = $_GET['status'] ?? null;
    $searchTerm = trim($_GET['search'] ?? '');

    $reportData = $attendanceModel->getAttendanceReport($startDate, $endDate, $beneficiaryId, null, $statusFilter, $searchTerm);
    $beneficiarySummary = $attendanceModel->getBeneficiaryAttendanceSummary($startDate, $endDate, $beneficiaryId, null, $statusFilter);
    $stats = $attendanceModel->getAttendanceStats($startDate, $endDate);
    $beneficiaries = $beneficiaryModel->getAllBeneficiaries(1000, 0);
    $selectedStartDate = $startDate;
    $selectedEndDate = $endDate;
    $selectedStatus = $statusFilter;
    $selectedBeneficiaryId = $beneficiaryId;
    $selectedSearch = $searchTerm;
    include __DIR__ . "/../views/attendance/report.php";
}

/**
 * HZ-ATT-CTRL-011A
 * Purpose: Export attendance data as CSV
 * Flow: Gather filtered records -> Stream CSV
 */
if ($action === 'export') {
    $mode = $_GET['mode'] ?? 'list';
    $startDate = $_GET['start_date'] ?? $_GET['date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? $_GET['date'] ?? date('Y-m-d');
    $statusFilter = $_GET['status'] ?? null;
    $beneficiaryId = isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : null;
    $searchTerm = trim($_GET['search'] ?? '');

    if ($mode === 'report') {
        $rows = $attendanceModel->getAttendanceReport($startDate, $endDate, $beneficiaryId, null, $statusFilter, $searchTerm);
        $filename = 'attendance_report_' . date('Y-m-d_His') . '.csv';
    } else {
        $rows = $attendanceModel->getAllAttendance(10000, 0, $_GET['date'] ?? null, $statusFilter, $beneficiaryId, $searchTerm);
        $filename = 'attendance_list_' . date('Y-m-d_His') . '.csv';
    }

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, ['AttendanceID', 'BeneficiaryID', 'BeneficiaryName', 'SessionDate', 'Status', 'Notes', 'RecordedAt', 'SessionType', 'Location']);

    foreach ($rows as $row) {
        $name = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
        fputcsv($output, [
            $row['AttendanceID'] ?? '',
            $row['BeneficiaryID'] ?? '',
            $name,
            $row['SessionDate'] ?? '',
            $row['Status'] ?? ($row['AttendanceStatus'] ?? ''),
            $row['Notes'] ?? '',
            $row['CreatedAt'] ?? '',
            $row['SessionType'] ?? '',
            $row['Location'] ?? '',
        ]);
    }

    fclose($output);
    exit();
}

/**
 * HZ-ATT-CTRL-012
 * Purpose: Handle bulk attendance save from dashboard
 * Flow: Receive JSON data -> Process bulk recording -> Return JSON response
 */
if ($action === 'bulk_save') {
    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['attendance']) || !is_array($input['attendance'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid attendance data']);
        exit();
    }

    $attendanceData = $input['attendance'];

    if (empty($attendanceData)) {
        echo json_encode(['success' => false, 'message' => 'No attendance data provided']);
        exit();
    }

    try {
        $results = [];
        $successCount = 0;

        foreach ($attendanceData as $record) {
            if (!isset($record['beneficiary_id']) || !isset($record['status']) || !isset($record['session_date'])) {
                continue;
            }

            $beneficiaryId = (int)$record['beneficiary_id'];
            $status = strtolower(trim((string)$record['status']));
            $sessionDate = $record['session_date'];

            // Validate status
            if (!in_array($status, ['present', 'absent', 'marked'], true)) {
                continue;
            }

            // Check if attendance already exists for this beneficiary on this date
            $existing = $attendanceModel->getAttendanceByBeneficiaryAndDate($beneficiaryId, $sessionDate);

            if ($existing) {
                // Update existing record
                $result = $attendanceModel->updateAttendance($existing['AttendanceID'], $beneficiaryId, $sessionDate, $status, '');
            } else {
                // Create new record
                $result = $attendanceModel->recordAttendance($beneficiaryId, $sessionDate, $status, '');
            }

            if ($result) {
                $successCount++;
            }

            $results[] = [
                'beneficiary_id' => $beneficiaryId,
                'success' => (bool)$result
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => "Attendance saved successfully. $successCount records processed.",
            'processed' => count($results),
            'successful' => $successCount
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
?>
