<?php
/**
 * Module: Volunteer Management & Scheduling
 * Purpose: Controller for volunteer CRUD operations and schedule management
 * Reference: Task 2b System Design Section 4.2 - Volunteer Management
 * Author: WIL Student
 */

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Volunteer.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";

// Require login
requireLogin();

// Only admins and staff can access this
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../views/dashboard.php?error=Access denied");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = "";
$success = "";

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Create volunteer model
    $volunteerModel = new Volunteer($db);

    /**
     * HZ-VOL-CTRL-001
     * Purpose: List all volunteers with pagination
     * Flow: Get volunteers -> Display in list view
     */
    if ($action === 'list') {
        $pageSize = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $pageSize;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $volunteers = $volunteerModel->getAllVolunteers($pageSize, $offset, $status);
        $statusCounts = $volunteerModel->getVolunteerCountByStatus();

        include __DIR__ . "/../views/volunteers/list.php";
    }

    /**
     * HZ-VOL-CTRL-002
     * Purpose: Display create volunteer form
     * Flow: Show registration form for new volunteer
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "GET") {
        include __DIR__ . "/../views/volunteers/create.php";
    }

    /**
     * HZ-VOL-CTRL-003
     * Purpose: Handle volunteer creation form submission
     * Flow: Validate input -> Create user -> Create volunteer profile
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $firstName = trim($_POST['first_name'] ?? "");
        $lastName = trim($_POST['last_name'] ?? "");
        $phone = trim($_POST['phone'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $email = trim($_POST['email'] ?? "");

        // Validation
        if (empty($firstName) || empty($lastName) || empty($phone) || empty($email)) {
            $error = "First name, last name, phone, and email are required";
        } else {
            try {
                // Create temporary password
                $tempPassword = bin2hex(random_bytes(4));

                // Create user account
                require_once __DIR__ . "/../models/User.php";
                $userModel = new User($db);

                // Generate username from first and last name
                $username = strtolower($firstName[0] . $lastName);
                $counter = 1;
                while ($userModel->findByUsername($username)) {
                    $username = strtolower($firstName[0] . $lastName . $counter);
                    $counter++;
                }

                $userId = $userModel->register($username, $email, $tempPassword, 'volunteer');

                if ($userId) {
                    // Create volunteer profile
                    $volunteerId = $volunteerModel->createVolunteer($userId, $firstName, $lastName, $phone, $address);

                    if ($volunteerId) {
                        $success = "Volunteer registered successfully! Username: " . htmlspecialchars($username) . ", Password: " . htmlspecialchars($tempPassword);
                        header("Refresh: 3; URL=VolunteerController.php?action=list");
                    } else {
                        $error = "Failed to create volunteer profile";
                    }
                } else {
                    $error = "Failed to create user account";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        include __DIR__ . "/../views/volunteers/create.php";
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
        $volunteerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
    include __DIR__ . "/../views/volunteers/list.php";
}
?>
