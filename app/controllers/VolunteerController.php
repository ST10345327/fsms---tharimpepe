<?php
/**
 * Module: Volunteer Management & Scheduling
 * Purpose: Controller for volunteer CRUD operations and schedule management
 * Reference: Task 2b System Design Section 4.2 - Volunteer Management
 * Author: WIL Student
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../helpers/bootstrap.php";

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../models/Volunteer.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../helpers/Rbac.php";

// Require login and volunteers permission (admin, staff)
requireLogin();
rbacRequirePermission('volunteers');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = "";
$success = "";
$volunteers = [];
$statusCounts = ['available' => 0, 'unavailable' => 0, 'on_leave' => 0];
$totalVolunteers = 0;
$totalPages = 1;
$page = 1;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Create volunteer model
    $volunteerModel = new Volunteer($db);
    $currentStatus = isset($_GET['status']) ? $_GET['status'] : null;
    $searchTerm = trim($_GET['q'] ?? '');

    /**
     * HZ-VOL-CTRL-001
     * Purpose: List all volunteers with pagination
     * Flow: Get volunteers -> Display in list view
     */
    if ($action === 'list') {
        $pageSize = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $pageSize;

        $volunteers = $volunteerModel->getAllVolunteers($pageSize, $offset, $currentStatus, $searchTerm !== '' ? $searchTerm : null);
        $statusCounts = $volunteerModel->getVolunteerCountByStatus();
        $totalVolunteers = $volunteerModel->getVolunteerCount($currentStatus, $searchTerm !== '' ? $searchTerm : null);
        $totalPages = max(1, (int)ceil($totalVolunteers / $pageSize));

        include __DIR__ . "/../views/volunteers/list.php";
    }

    /**
     * HZ-VOL-CTRL-002
     * Purpose: Display create volunteer form - DISABLED for admin
     * Flow: Volunteers must self-register via the public registration page
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "GET") {
        $_SESSION['error'] = "Volunteers must register themselves via the public registration page at /index.php?action=register";
        header("Location: VolunteerController.php?action=list");
        exit();
    }

    /**
     * HZ-VOL-CTRL-003
     * Purpose: Handle volunteer creation form submission - DISABLED for admin
     * Flow: Volunteers must self-register via the public registration page
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $_SESSION['error'] = "Volunteers must register themselves via the public registration page at /index.php?action=register";
        header("Location: VolunteerController.php?action=list");
        exit();
    }

    /**
     * HZ-VOL-CTRL-004
     * Purpose: Display volunteer profile for editing
     * Flow: Get volunteer -> Show edit form
     */
    if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "GET") {
        $volunteerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($volunteerId <= 0) {
            header("Location: VolunteerController.php?action=list&error=Invalid volunteer ID");
            exit();
        }

        $volunteer = $volunteerModel->getVolunteerById($volunteerId);

        if (!$volunteer) {
            header("Location: VolunteerController.php?action=list&error=Volunteer not found");
            exit();
        }

        include __DIR__ . "/../views/volunteers/edit.php";
    }

    /**
     * HZ-VOL-CTRL-005
     * Purpose: Handle volunteer profile update
     * Flow: Validate input -> Update database -> Redirect
     */
    if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Security validation failed. Please try again.";
        } else {
        $volunteerId = isset($_POST['volunteer_id']) ? (int)$_POST['volunteer_id'] : 0;
        $firstName = trim($_POST['first_name'] ?? "");
        $lastName = trim($_POST['last_name'] ?? "");
        $phone = trim($_POST['phone'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $status = $_POST['status'] ?? "available";

        if (empty($firstName) || empty($lastName) || empty($phone)) {
            $error = "First name, last name, and phone are required";
        } else {
            try {
                if ($volunteerModel->updateVolunteer($volunteerId, $firstName, $lastName, $phone, $address, $status)) {
                    $success = "Volunteer profile updated successfully!";
                    header("Refresh: 2; URL=VolunteerController.php?action=view&id=" . $volunteerId);
                } else {
                    $error = "Failed to update volunteer profile";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        }

        $volunteer = $volunteerModel->getVolunteerById($volunteerId);
        include __DIR__ . "/../views/volunteers/edit.php";
    }

    /**
     * HZ-VOL-CTRL-006
     * Purpose: Display volunteer profile view
     * Flow: Get volunteer -> Show profile
     */
    if ($action === 'view') {
        $volunteerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($volunteerId <= 0) {
            header("Location: VolunteerController.php?action=list&error=Invalid volunteer ID");
            exit();
        }

        $volunteer = $volunteerModel->getVolunteerById($volunteerId);

        if (!$volunteer) {
            header("Location: VolunteerController.php?action=list&error=Volunteer not found");
            exit();
        }

        include __DIR__ . "/../views/volunteers/view.php";
    }

    /**
     * HZ-VOL-CTRL-007
     * Purpose: Update volunteer availability status
     * Flow: Parse action parameter -> Update status -> Return JSON
     */
    if ($action === 'update-status') {
        header('Content-Type: application/json');

        $volunteerId = isset($_POST['volunteer_id']) ? (int)$_POST['volunteer_id'] : 0;
        $status = $_POST['status'] ?? "available";

        if ($volunteerId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid volunteer ID']);
            exit();
        }

        try {
            if ($volunteerModel->updateAvailabilityStatus($volunteerId, $status)) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit();
    }

    /**
     * HZ-VOL-CTRL-007A
     * Purpose: Export volunteer list as CSV
     */
    if ($action === 'export') {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $search = trim($_GET['q'] ?? '');
        $rows = $volunteerModel->getVolunteersForExport($status, $search !== '' ? $search : null);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=fsms_volunteers.xls');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Volunteers</x:Name></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        echo '<style>td,th{padding:4px 8px;vertical-align:middle}th{font-weight:bold;background:#667eea;color:#fff;text-align:center}td{text-align:left}</style></head><body>';
        echo '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px">';
        echo '<tr><th>VolunteerID</th><th>Username</th><th>Email</th><th>FullName</th><th>Phone</th><th>Address</th><th>AvailabilityStatus</th><th>CreatedAt</th></tr>';

        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td style="text-align:center">' . htmlspecialchars((string)$row['VolunteerID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['Username'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['Email'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['FullName'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['Phone'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['Address'] ?? '') . '</td>';
            echo '<td style="text-align:center">' . htmlspecialchars($row['AvailabilityStatus'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['CreatedAt'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit();
    }

    /**
     * HZ-VOL-CTRL-008
     * Purpose: Search volunteers by name or phone
     * Flow: Validate search term -> Return matching volunteers
     */
    if ($action === 'search') {
        $searchTerm = trim($_GET['q'] ?? "");

        if (strlen($searchTerm) < 2) {
            $volunteers = [];
        } else {
            $volunteers = $volunteerModel->searchVolunteers($searchTerm);
        }

        include __DIR__ . "/../views/volunteers/search-results.php";
    }

    /**
     * HZ-VOL-CTRL-009
     * Purpose: Delete volunteer (deactivate account)
     * Flow: Verify volunteer exists -> Soft delete
     */
    if ($action === 'delete') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: VolunteerController.php?action=list&error=" . urlencode("Invalid delete request"));
            exit();
        }

        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            header("Location: VolunteerController.php?action=list&error=" . urlencode("Security validation failed. Please try again."));
            exit();
        }

        $volunteerId = isset($_POST['volunteer_id']) ? (int)$_POST['volunteer_id'] : 0;

        if ($volunteerId <= 0) {
            $error = "Invalid volunteer ID";
        } else {
            try {
                if ($volunteerModel->deleteVolunteer($volunteerId)) {
                    $success = "Volunteer has been deactivated successfully";
                    header("Refresh: 2; URL=VolunteerController.php?action=list");
                } else {
                    $error = "Failed to deactivate volunteer";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if ($error) {
            header("Location: VolunteerController.php?action=list&error=" . urlencode($error));
        }
        exit();
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    $statusCounts = ['available' => 0, 'unavailable' => 0, 'on_leave' => 0];
    $volunteers = [];
    $totalVolunteers = 0;
    $totalPages = 1;
    include __DIR__ . "/../views/volunteers/list.php";
}
?>
