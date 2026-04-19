<?php
/**
 * Module: Beneficiary Management & Registration
 * Purpose: Controller for beneficiary CRUD operations and meal recipient management
 * Reference: Task 2b System Design Section 4.3 - Beneficiary Management
 * Author: WIL Student
 */

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Beneficiary.php";
require_once __DIR__ . "/../models/ActivityLog.php";
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

    // Create beneficiary model
    $beneficiaryModel = new Beneficiary($db);

    /**
     * HZ-BEN-CTRL-001
     * Purpose: List all beneficiaries with pagination
     * Flow: Get beneficiaries -> Display in list view
     */
    if ($action === 'list') {
        $pageSize = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $pageSize;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $beneficiaries = $beneficiaryModel->getAllBeneficiaries($pageSize, $offset, $status);
        $statusCounts = $beneficiaryModel->getBeneficiaryCountByStatus();
        $totalCount = $beneficiaryModel->getTotalCount();

        include __DIR__ . "/../views/beneficiaries/list.php";
    }

    /**
     * HZ-BEN-CTRL-002
     * Purpose: Display create beneficiary form
     * Flow: Show registration form for new beneficiary
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "GET") {
        include __DIR__ . "/../views/beneficiaries/create.php";
    }

    /**
     * HZ-BEN-CTRL-003
     * Purpose: Handle beneficiary creation form submission
     * Flow: Validate input -> Create beneficiary record
     */
    if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $firstName = trim($_POST['first_name'] ?? "");
        $lastName = trim($_POST['last_name'] ?? "");
        $dateOfBirth = $_POST['date_of_birth'] ?? "";
        $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
        $guardianName = trim($_POST['guardian_name'] ?? "");
        $contactNumber = trim($_POST['contact_number'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $category = $_POST['category'] ?? "";
        $registrationDate = $_POST['registration_date'] ?? "";

        // Calculate age from date of birth
        $age = null;
        if (!empty($dateOfBirth)) {
            $birthDate = new DateTime($dateOfBirth);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
        }

        // Validation
        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            $error = "First name, last name, and registration date are required";
        } else {
            try {
                $beneficiaryId = $beneficiaryModel->createBeneficiary($firstName, $lastName, $age, $gender, $contactNumber, null, $address, $registrationDate, $guardianName . " | " . $category);

                if ($beneficiaryId) {
                    ActivityLog::log(getCurrentUser()['user_id'], 'create_beneficiary', 'Beneficiary', $beneficiaryId, "Created beneficiary: $firstName $lastName");
                    $success = "Beneficiary registered successfully!";
                    header("Location: ../views/dashboard.php?success=Beneficiary registered successfully");
                    exit();
                } else {
                    $error = "Failed to register beneficiary";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        include __DIR__ . "/../views/beneficiaries/create.php";
    }

    /**
     * HZ-BEN-CTRL-004
     * Purpose: Display beneficiary profile for editing
     * Flow: Get beneficiary -> Show edit form
     */
    if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "GET") {
        $beneficiaryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($beneficiaryId <= 0) {
            header("Location: BeneficiaryController.php?action=list&error=Invalid beneficiary ID");
            exit();
        }

        $beneficiary = $beneficiaryModel->getBeneficiaryById($beneficiaryId);

        if (!$beneficiary) {
            header("Location: BeneficiaryController.php?action=list&error=Beneficiary not found");
            exit();
        }

        include __DIR__ . "/../views/beneficiaries/edit.php";
    }

    /**
     * HZ-BEN-CTRL-005
     * Purpose: Handle beneficiary profile update
     * Flow: Validate input -> Update database -> Redirect
     */
    if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;
        $firstName = trim($_POST['firstName'] ?? "");
        $lastName = trim($_POST['lastName'] ?? "");
        $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
        $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
        $phone = trim($_POST['phone'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $registrationDate = $_POST['registrationDate'] ?? "";
        $status = $_POST['status'] ?? "active";
        $notes = trim($_POST['notes'] ?? "");

        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            $error = "First name, last name, and registration date are required";
        } else {
            try {
                if ($beneficiaryModel->updateBeneficiary($beneficiaryId, $firstName, $lastName, $age, $gender, $phone, $email, $address, $registrationDate, $status, $notes)) {
                    ActivityLog::log(getCurrentUser()['user_id'], 'update_beneficiary', 'Beneficiary', $beneficiaryId, "Updated beneficiary: $firstName $lastName (Status: $status)");
                    $success = "Beneficiary profile updated successfully!";
                    header("Refresh: 2; URL=BeneficiaryController.php?action=view&id=" . $beneficiaryId);
                } else {
                    $error = "Failed to update beneficiary profile";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        $beneficiary = $beneficiaryModel->getBeneficiaryById($beneficiaryId);
        include __DIR__ . "/../views/beneficiaries/edit.php";
    }

    /**
     * HZ-BEN-CTRL-006
     * Purpose: Display beneficiary profile view
     * Flow: Get beneficiary -> Show profile
     */
    if ($action === 'view') {
        $beneficiaryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($beneficiaryId <= 0) {
            header("Location: BeneficiaryController.php?action=list&error=Invalid beneficiary ID");
            exit();
        }

        $beneficiary = $beneficiaryModel->getBeneficiaryById($beneficiaryId);

        if (!$beneficiary) {
            header("Location: BeneficiaryController.php?action=list&error=Beneficiary not found");
            exit();
        }

        include __DIR__ . "/../views/beneficiaries/view.php";
    }

    /**
     * HZ-BEN-CTRL-007
     * Purpose: Update beneficiary status
     * Flow: Parse action parameter -> Update status -> Return JSON
     */
    if ($action === 'update-status') {
        header('Content-Type: application/json');

        $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;
        $status = $_POST['status'] ?? "active";

        if ($beneficiaryId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid beneficiary ID']);
            exit();
        }

        try {
            if ($beneficiaryModel->updateStatus($beneficiaryId, $status)) {
                ActivityLog::log(getCurrentUser()['user_id'], 'update_beneficiary_status', 'Beneficiary', $beneficiaryId, "Changed status to: $status");
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
     * HZ-BEN-CTRL-008
     * Purpose: Search beneficiaries by name or notes
     * Flow: Validate search term -> Return matching beneficiaries
     */
    if ($action === 'search') {
        $searchTerm = trim($_GET['q'] ?? "");

        if (strlen($searchTerm) < 2) {
            $beneficiaries = [];
        } else {
            $beneficiaries = $beneficiaryModel->searchBeneficiaries($searchTerm);
        }

        include __DIR__ . "/../views/beneficiaries/search-results.php";
    }

    /**
     * HZ-BEN-CTRL-009
     * Purpose: Get beneficiaries by date range
     * Flow: Validate dates -> Return beneficiaries in range
     */
    if ($action === 'by-date-range') {
        $startDate = $_GET['start_date'] ?? "";
        $endDate = $_GET['end_date'] ?? "";

        if (empty($startDate) || empty($endDate)) {
            $error = "Start date and end date are required";
            $beneficiaries = [];
        } else {
            try {
                $beneficiaries = $beneficiaryModel->getBeneficiariesByDateRange($startDate, $endDate);
            } catch (Exception $e) {
                $error = $e->getMessage();
                $beneficiaries = [];
            }
        }

        include __DIR__ . "/../views/beneficiaries/date-range-results.php";
    }

    /**
     * HZ-BEN-CTRL-010
     * Purpose: Get beneficiaries by age range
     * Flow: Validate age range -> Return beneficiaries in range
     */
    if ($action === 'by-age-range') {
        $minAge = isset($_GET['min_age']) ? (int)$_GET['min_age'] : 0;
        $maxAge = isset($_GET['max_age']) ? (int)$_GET['max_age'] : 120;

        if ($minAge < 0 || $maxAge > 120 || $minAge > $maxAge) {
            $error = "Invalid age range";
            $beneficiaries = [];
        } else {
            try {
                $beneficiaries = $beneficiaryModel->getBeneficiariesByAgeRange($minAge, $maxAge);
            } catch (Exception $e) {
                $error = $e->getMessage();
                $beneficiaries = [];
            }
        }

        include __DIR__ . "/../views/beneficiaries/age-range-results.php";
    }

    /**
     * HZ-BEN-CTRL-011
     * Purpose: Delete beneficiary record
     * Flow: Verify beneficiary exists -> Hard delete
     */
    if ($action === 'delete') {
        $beneficiaryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($beneficiaryId <= 0) {
            $error = "Invalid beneficiary ID";
        } else {
            try {
                if ($beneficiaryModel->deleteBeneficiary($beneficiaryId)) {
                    ActivityLog::log(getCurrentUser()['user_id'], 'delete_beneficiary', 'Beneficiary', $beneficiaryId, "Deleted beneficiary record");
                    $success = "Beneficiary record has been permanently deleted";
                    header("Refresh: 2; URL=BeneficiaryController.php?action=list");
                } else {
                    $error = "Failed to delete beneficiary record";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if ($error) {
            header("Location: BeneficiaryController.php?action=list&error=" . urlencode($error));
        }
        exit();
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    include __DIR__ . "/../views/beneficiaries/list.php";
}
?>
