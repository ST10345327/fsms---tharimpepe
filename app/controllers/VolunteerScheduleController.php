<?php
/**
 * Module: Volunteer Scheduling Controller
 * Purpose: Manage volunteer schedule operations
 * Reference: HZ-SCHED-CTRL-001 to HZ-SCHED-CTRL-012
 * Author: WIL Student
 */

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/VolunteerSchedule.php";
require_once __DIR__ . "/../models/ActivityLog.php";

requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : '';

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
    
    $conn = getConnection();
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM VolunteerSchedules WHERE 1=1");
    $countStmt->execute();
    $totalSchedules = $countStmt->fetchColumn();
    $totalPages = ceil($totalSchedules / $limit);
    
    include __DIR__ . "/../views/schedules/list.php";
}

function showCreateForm() {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT VolunteerID, FullName FROM Volunteers WHERE Status = 'active' ORDER BY FullName ASC");
    $stmt->execute();
    $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    include __DIR__ . "/../views/schedules/create.php";
}

function storeSchedule() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
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
    $scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $schedule = VolunteerSchedule::getScheduleById($scheduleId);
    if (!$schedule) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/schedules/edit.php";
}

function updateSchedule() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    $scheduleId = (int)$_POST['id'];
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
    $scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $schedule = VolunteerSchedule::getScheduleById($scheduleId);
    if (!$schedule) {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/schedules/delete.php";
}

function destroySchedule() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    $scheduleId = (int)$_POST['id'];
    
    if (VolunteerSchedule::deleteSchedule($scheduleId)) {
        ActivityLog::log(getCurrentUser()['user_id'], 'delete_schedule', 'VolunteerSchedule', $scheduleId, "Deleted schedule");
        $_SESSION['success'] = "Schedule deleted successfully";
    }
    
    header("Location: VolunteerScheduleController.php?action=list");
    exit;
}

function manageAvailability() {
    $volunteerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $availability = VolunteerSchedule::getVolunteerAvailability($volunteerId);
    
    // Ensure all days exist
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $availabilityMap = [];
    
    foreach ($availability as $av) {
        $availabilityMap[$av['DayOfWeek']] = $av;
    }
    
    // Fill in missing days
    foreach ($daysOfWeek as $day) {
        if (!isset($availabilityMap[$day])) {
            $availabilityMap[$day] = ['DayOfWeek' => $day, 'IsAvailable' => 0, 'Notes' => ''];
        }
    }
    
    include __DIR__ . "/../views/schedules/availability.php";
}

function saveAvailability() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: VolunteerScheduleController.php?action=list");
        exit;
    }
    
    $volunteerId = (int)$_POST['volunteer_id'];
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    foreach ($daysOfWeek as $day) {
        $isAvailable = isset($_POST["available_$day"]) ? 1 : 0;
        $notes = $_POST["notes_$day"] ?? '';
        
        VolunteerSchedule::setAvailability($volunteerId, $day, $isAvailable, $notes);
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
    
    $scheduleDate = $_GET['date'];
    $schedules = VolunteerSchedule::getSchedulesByDateRange($scheduleDate, $scheduleDate);
    
    include __DIR__ . "/../views/schedules/shifts.php";
}

function scheduleReport() {
    $fromDate = $_GET['from_date'] ?? date('Y-m-01');
    $toDate = $_GET['to_date'] ?? date('Y-m-d');
    
    $schedules = VolunteerSchedule::getSchedulesByDateRange($fromDate, $toDate);
    $stats = VolunteerSchedule::getScheduleStats();
    
    include __DIR__ . "/../views/schedules/report.php";
}
?>
