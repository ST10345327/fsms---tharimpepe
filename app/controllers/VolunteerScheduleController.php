<?php
/**
 * Module: Volunteer Scheduling Controller
 * Purpose: Manage volunteer schedule operations
 * Reference: HZ-SCHED-CTRL-001 to HZ-SCHED-CTRL-012
 * Author: WIL Student
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../helpers/bootstrap.php";

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../helpers/Rbac.php";
require_once __DIR__ . "/../models/Volunteer.php";
require_once __DIR__ . "/../models/VolunteerSchedule.php";
require_once __DIR__ . "/../models/ActivityLog.php";

requireLogin();

// HZ-SCHED-CTRL-RBAC: Enforce schedule access (admin, staff, volunteer)
rbacRequirePermission('schedules');

$action = isset($_GET['action']) ? $_GET['action'] : '';

function requireScheduleManagementRole() {
    $role = strtolower((string)(getCurrentUser()['role'] ?? ''));
    if (!in_array($role, ['admin', 'staff'], true)) {
        rbacDenyWeb('You do not have permission to manage volunteer schedules.');
    }
}

switch ($action) {
    case 'list':
        listSchedules();
        break;
    case 'create':
        showCreateForm();
        break;
    case 'store':
        storeSchedule();
        break;
    case 'view':
        viewSchedule();
        break;
    case 'edit':
        showEditForm();
        break;
    case 'update':
        updateSchedule();
        break;
    case 'delete':
        deleteScheduleConfirm();
        break;
    case 'destroy':
        destroySchedule();
        break;
    case 'availability':
        manageAvailability();
        break;
    case 'save_availability':
        saveAvailability();
        break;
    case 'shifts':
        viewShifts();
        break;
    case 'report':
        scheduleReport();
        break;
    default:
        listSchedules();
}

function listSchedules() {
    $limit = 20;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    $filters = [];
    if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
    if (isset($_GET['volunteer_id'])) $filters['volunteer_id'] = $_GET['volunteer_id'];
    if (isset($_GET['from_date'])) $filters['from_date'] = $_GET['from_date'];
    if (isset($_GET['to_date'])) $filters['to_date'] = $_GET['to_date'];
    
    $schedules = VolunteerSchedule::getAllSchedules($limit, $offset, $filters);
    $stats = VolunteerSchedule::getScheduleStats();
    $totalSchedules = VolunteerSchedule::getScheduleCount($filters);
    $totalPages = max(1, (int)ceil($totalSchedules / $limit));
    
    include __DIR__ . "/../views/schedules/list.php";
}

function showCreateForm() {
    requireScheduleManagementRole();
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT v.VolunteerID, u.FullName
                            FROM Volunteers v
                            INNER JOIN Users u ON v.UserID = u.UserID
                            WHERE u.Status = 'active' AND v.Status = 'approved'
                            ORDER BY u.FullName ASC");
    $stmt->execute();
    $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    include __DIR__ . "/../views/schedules/create.php";
}

function storeSchedule() {
    requireScheduleManagementRole();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: VolunteerScheduleController.php?action=create");
        exit;
    }
    
    $volunteerId = (int)$_POST['volunteer_id'];
    $scheduleDate = $_POST['schedule_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $location = $_POST['location'] ?? '';
    $role = $_POST['role'] ?? 'Assistant';
    $notes = $_POST['notes'] ?? '';
    
    if (VolunteerSchedule::createSchedule($volunteerId, $scheduleDate, $startTime, $endTime, $location, $role, $notes)) {
        ActivityLog::log(getCurrentUser()['user_id'], 'create_schedule', 'VolunteerSchedule', $volunteerId, "Created schedule for $scheduleDate");
        $_SESSION['success'] = "Schedule created successfully";
        header("Location: VolunteerScheduleController.php?action=list");
    } else {
        $_SESSION['error'] = "Error creating schedule";
        header("Location: VolunteerScheduleController.php?action=create");
    }
    exit;
}

function viewSchedule() {
    $scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $schedule = VolunteerSchedule::getScheduleById($scheduleId);
    if (!$schedule) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/schedules/view.php";
}

function showEditForm() {
    requireScheduleManagementRole();
    $scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $schedule = VolunteerSchedule::getScheduleById($scheduleId);
    if (!$schedule) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/schedules/edit.php";
}

function updateSchedule() {
    requireScheduleManagementRole();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: VolunteerScheduleController.php?action=edit&id=" . (int)($_POST['id'] ?? $_POST['schedule_id'] ?? 0));
        exit;
    }
    
    $scheduleId = (int)($_POST['id'] ?? $_POST['schedule_id'] ?? 0);
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $location = $_POST['location'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';
    $notes = $_POST['notes'] ?? '';
    
    if (VolunteerSchedule::updateSchedule($scheduleId, $startTime, $endTime, $location, $role, $status, $notes)) {
        ActivityLog::log(getCurrentUser()['user_id'], 'update_schedule', 'VolunteerSchedule', $scheduleId, "Updated schedule");
        $_SESSION['success'] = "Schedule updated successfully";
        header("Location: VolunteerScheduleController.php?action=view&id=$scheduleId");
    } else {
        $_SESSION['error'] = "Error updating schedule";
        header("Location: VolunteerScheduleController.php?action=edit&id=$scheduleId");
    }
    exit;
}

function deleteScheduleConfirm() {
    requireScheduleManagementRole();
    $scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $schedule = VolunteerSchedule::getScheduleById($scheduleId);
    if (!$schedule) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/schedules/delete.php";
}

function destroySchedule() {
    requireScheduleManagementRole();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    $scheduleId = (int)($_POST['id'] ?? $_POST['schedule_id'] ?? 0);
    
    if (VolunteerSchedule::deleteSchedule($scheduleId)) {
        ActivityLog::log(getCurrentUser()['user_id'], 'delete_schedule', 'VolunteerSchedule', $scheduleId, "Deleted schedule");
        $_SESSION['success'] = "Schedule deleted successfully";
    }
    
    header("Location: VolunteerScheduleController.php?action=list");
    exit;
}

function manageAvailability() {
    $volunteerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($volunteerId <= 0) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }

    $volunteerModel = new Volunteer(getConnection());
    $volunteer = $volunteerModel->getVolunteerById($volunteerId);
    if (!$volunteer) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }

    $currentUser = getCurrentUser();
    if ($currentUser && strtolower((string)$currentUser['role']) === 'volunteer') {
        $currentVolunteer = $volunteerModel->getVolunteerByUserId((int)$currentUser['user_id']);
        if (!$currentVolunteer || (int)$currentVolunteer['VolunteerID'] !== $volunteerId) {
            rbacDenyWeb('You can only update your own availability.');
        }
    }

    $availability = VolunteerSchedule::getVolunteerAvailability($volunteerId);
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $availabilityMap = [];

    foreach ($daysOfWeek as $day) {
        $availabilityMap[$day] = ['DayOfWeek' => $day, 'IsAvailable' => 0, 'Notes' => ''];
    }

    foreach ($availability as $av) {
        $availabilityMap[$av['DayOfWeek']] = $av;
    }

    $availability = $availabilityMap;
    
    include __DIR__ . "/../views/schedules/availability.php";
}

function saveAvailability() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: VolunteerScheduleController.php?action=availability&id=" . (int)$_POST['volunteer_id']);
        exit;
    }
    
    $volunteerId = (int)$_POST['volunteer_id'];
    $currentUser = getCurrentUser();
    if ($currentUser && strtolower((string)$currentUser['role']) === 'volunteer') {
        $volunteerModel = new Volunteer(getConnection());
        $currentVolunteer = $volunteerModel->getVolunteerByUserId((int)$currentUser['user_id']);
        if (!$currentVolunteer || (int)$currentVolunteer['VolunteerID'] !== $volunteerId) {
            rbacDenyWeb('You can only update your own availability.');
        }
    }

    $availabilityRows = $_POST['availability'] ?? [];
    foreach ($availabilityRows as $row) {
        $day = $row['day_of_week'] ?? '';
        $isAvailable = isset($row['is_available']) ? (int)$row['is_available'] : 0;
        $notes = trim($row['notes'] ?? '');

        if ($day) {
            VolunteerSchedule::setAvailability($volunteerId, $day, $isAvailable, $notes);
        }
    }
    
    ActivityLog::log(getCurrentUser()['user_id'], 'update_availability', 'VolunteerAvailability', $volunteerId, "Updated volunteer availability");
    $_SESSION['success'] = "Availability updated successfully";
    
    header("Location: VolunteerScheduleController.php?action=availability&id=$volunteerId");
    exit;
}

function viewShifts() {
    if (!isset($_GET['date'])) {
        $_GET['date'] = date('Y-m-d');
    }
    
    $selectedDate = $_GET['date'];
    $shifts = VolunteerSchedule::getSchedulesByDateRange($selectedDate, $selectedDate);
    
    include __DIR__ . "/../views/schedules/shifts.php";
}

function scheduleReport() {
    $fromDate = $_GET['from_date'] ?? date('Y-m-01');
    $toDate = $_GET['to_date'] ?? date('Y-m-d');
    $status = $_GET['status'] ?? null;
    
    $scheduleData = VolunteerSchedule::getSchedulesByDateRange($fromDate, $toDate);
    $stats = VolunteerSchedule::getScheduleStats();
    $volunteerStats = VolunteerSchedule::getVolunteerScheduleSummary($fromDate, $toDate, $status);
    $recentSchedules = array_slice($scheduleData, 0, 10);
    
    include __DIR__ . "/../views/schedules/report.php";
}
?>
