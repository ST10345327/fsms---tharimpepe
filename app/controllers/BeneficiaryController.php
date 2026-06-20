<?php
/**
 * Module: Beneficiary Management & Registration
 * Purpose: Controller for beneficiary CRUD operations and meal recipient management
 * Reference: Task 2b System Design Section 4.3 - Beneficiary Management
 * Author: WIL Student
 */

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/Beneficiary.php";
require_once __DIR__ . "/../models/ActivityLog.php";
require_once __DIR__ . "/../helpers/db.php";
require_once __DIR__ . "/../../config/database.php";

requireLogin();
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../views/dashboard.php?error=Access denied");
    exit();
}

$action = $_GET['action'] ?? 'list';
$error = "";
$success = "";

$pdo = getDBConnection();
$beneficiaryModel = new Beneficiary($pdo);

try {
    if ($action === 'list') {
        // Pagination parameters
        $pageSize = 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $pageSize;
        $status = $_GET['status'] ?? null;

        // Retrieve data
        $beneficiaries = $beneficiaryModel->getAllBeneficiaries($pageSize, $offset, $status);
        $statusCounts = $beneficiaryModel->getBeneficiaryCountByStatus();
        $totalCount = $beneficiaryModel->getTotalCount();

        // Render view
        include __DIR__ . "/../views/beneficiaries/list.php";
    }

    /**
     * HZ-BEN-CTRL-001
     * Purpose: Display create beneficiary form
     * Flow: Show registration form for new beneficiary
     */
    elseif ($action === 'create') {
        include __DIR__ . "/../views/beneficiaries/create.php";
    }

    /**
     * HZ-BEN-CTRL-002
     * Purpose: Store new beneficiary record
     * Flow: Validate input -> Insert beneficiary -> Redirect to list
     */
    elseif ($action === 'store') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method");
        }

        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception("Security validation failed. Please try again.");
        }

        $firstName = trim($_POST['firstName'] ?? "");
        $lastName = trim($_POST['lastName'] ?? "");
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $gender = $_POST['gender'] ?? "";
        $phone = trim($_POST['phone'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $registrationDate = $_POST['registrationDate'] ?? "";
        $status = $_POST['status'] ?? "active";
        $notes = trim($_POST['notes'] ?? "");

        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            throw new Exception("First name, last name, and registration date are required");
        }

        if ($beneficiaryModel->createBeneficiary($firstName, $lastName, $age, $gender, $phone, $email, $address, $registrationDate, $notes)) {
            $newId = $pdo->lastInsertId();
            ActivityLog::log(getCurrentUser()['user_id'], 'create_beneficiary', 'Beneficiary', $newId, "Created beneficiary: $firstName $lastName");
            $success = "Beneficiary registered successfully!";
            header("Refresh: 2; URL=BeneficiaryController.php?action=view&id=" . $newId);
        } else {
            throw new Exception("Failed to register beneficiary");
        }

        include __DIR__ . "/../views/beneficiaries/list.php";
    }

    /**
     * HZ-BEN-CTRL-003
     * Purpose: Display edit beneficiary form
     * Flow: Get beneficiary by ID -> Show edit form
     */
    elseif ($action === 'edit') {
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
     * HZ-BEN-CTRL-004
     * Purpose: Update beneficiary record
     * Flow: Validate input -> Update beneficiary -> Redirect to view
     */
    elseif ($action === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Invalid request method");
        }

        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception("Security validation failed. Please try again.");
        }

        $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;

        if ($beneficiaryId <= 0) {
            throw new Exception("Invalid beneficiary ID");
        }

        $firstName = trim($_POST['firstName'] ?? "");
        $lastName = trim($_POST['lastName'] ?? "");
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $gender = $_POST['gender'] ?? "";
        $phone = trim($_POST['phone'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $address = trim($_POST['address'] ?? "");
        $registrationDate = $_POST['registrationDate'] ?? "";
        $status = $_POST['status'] ?? "active";
        $notes = trim($_POST['notes'] ?? "");

        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            throw new Exception("First name, last name, and registration date are required");
        }

        if ($beneficiaryModel->updateBeneficiary($beneficiaryId, $firstName, $lastName, $age, $gender, $phone, $email, $address, $registrationDate, $status, $notes)) {
            ActivityLog::log(getCurrentUser()['user_id'], 'update_beneficiary', 'Beneficiary', $beneficiaryId, "Updated beneficiary: $firstName $lastName (Status: $status)");
            $success = "Beneficiary profile updated successfully!";
            header("Refresh: 2; URL=BeneficiaryController.php?action=view&id=" . $beneficiaryId);
        } else {
            throw new Exception("Failed to update beneficiary profile");
        }

        $beneficiary = $beneficiaryModel->getBeneficiaryById($beneficiaryId);
        include __DIR__ . "/../views/beneficiaries/edit.php";
    }

    /**
     * HZ-BEN-CTRL-005
     * Purpose: Display beneficiary profile view
     * Flow: Get beneficiary -> Show profile
     */
    elseif ($action === 'view') {
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
     * HZ-BEN-CTRL-006
     * Purpose: Update beneficiary status (AJAX endpoint)
     * Flow: Parse action parameter -> Update status -> Return JSON
     */
    elseif ($action === 'update-status') {
        header('Content-Type: application/json');

        $beneficiaryId = isset($_POST['beneficiary_id']) ? (int)$_POST['beneficiary_id'] : 0;
        $status = $_POST['status'] ?? "active";

        if ($beneficiaryId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid beneficiary ID']);
            exit();
        }

        if ($beneficiaryModel->updateStatus($beneficiaryId, $status)) {
            ActivityLog::log(getCurrentUser()['user_id'], 'update_beneficiary_status', 'Beneficiary', $beneficiaryId, "Changed status to: $status");
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }

        exit();
    }

    /**
     * HZ-BEN-CTRL-007
     * Purpose: Search beneficiaries by name or notes
     * Flow: Validate search term -> Return matching beneficiaries
     */
    elseif ($action === 'search') {
        $searchTerm = trim($_GET['q'] ?? "");

        if (strlen($searchTerm) < 2) {
            $beneficiaries = [];
        } else {
            $beneficiaries = $beneficiaryModel->searchBeneficiaries($searchTerm);
        }

        include __DIR__ . "/../views/beneficiaries/search-results.php";
    }

    /**
     * HZ-BEN-CTRL-008
     * Purpose: Get beneficiaries by date range
     * Flow: Validate dates -> Return beneficiaries in range
     */
    elseif ($action === 'by-date-range') {
        $startDate = $_GET['start_date'] ?? "";
        $endDate = $_GET['end_date'] ?? "";

        if (empty($startDate) || empty($endDate)) {
            $error = "Start date and end date are required";
            $beneficiaries = [];
        } else {
            $beneficiaries = $beneficiaryModel->getBeneficiariesByDateRange($startDate, $endDate);
        }

        include __DIR__ . "/../views/beneficiaries/date-range-results.php";
    }

    /**
     * HZ-BEN-CTRL-009
     * Purpose: Get beneficiaries by age range
     * Flow: Validate age range -> Return beneficiaries in range
     */
    elseif ($action === 'by-age-range') {
        $minAge = isset($_GET['min_age']) ? (int)$_GET['min_age'] : 0;
        $maxAge = isset($_GET['max_age']) ? (int)$_GET['max_age'] : 120;

        if ($minAge < 0 || $maxAge > 120 || $minAge > $maxAge) {
            $error = "Invalid age range";
            $beneficiaries = [];
        } else {
            $beneficiaries = $beneficiaryModel->getBeneficiariesByAgeRange($minAge, $maxAge);
        }

        include __DIR__ . "/../views/beneficiaries/age-range-results.php";
    }

    /**
     * HZ-BEN-CTRL-010
     * Purpose: Delete beneficiary record
     * Flow: Verify beneficiary exists -> Hard delete
     */
    elseif ($action === 'delete') {
        $beneficiaryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($beneficiaryId <= 0) {
            $error = "Invalid beneficiary ID";
        } else {
            // CSRF validation for delete action
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $error = "Security validation failed. Please try again.";
                header("Location: BeneficiaryController.php?action=list&error=" . urlencode($error));
                exit();
            }

            if ($beneficiaryModel->deleteBeneficiary($beneficiaryId)) {
                ActivityLog::log(getCurrentUser()['user_id'], 'delete_beneficiary', 'Beneficiary', $beneficiaryId, "Deleted beneficiary record");
                $success = "Beneficiary record has been permanently deleted";
                header("Refresh: 2; URL=BeneficiaryController.php?action=list");
            } else {
                $error = "Failed to delete beneficiary record";
            }
        }

        if ($error) {
            header("Location: BeneficiaryController.php?action=list&error=" . urlencode($error));
        }
        exit();
    } else {
        // Unknown action, default to list
        header("Location: BeneficiaryController.php?action=list");
        exit();
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    include __DIR__ . "/../views/beneficiaries/list.php";
}
?>