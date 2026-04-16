<?php
/**
 * File: DonationController.php
 * Purpose: Workflow controller for donation management and tracking
 * Author: FSMS Development Agent
 * Architecture Layer: Domain (Controller)
 * Reference: MVC Pattern (Satzinger, Jackson & Burd, 2014)
 * 
 * Reference Code Base:
 * [1] MVC Controller Pattern (Satzinger et al., 2014)
 * [2] PHP Session Management (PHP Manual, 2025)
 * [3] Web Security Best Practices (OWASP, 2025)
 */

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/Donation.php";
require_once __DIR__ . "/../config/database.php";

// HZ-DON-CTRL-001: Require user authentication and authorization
requireLogin();
$currentUser = getCurrentUser();

// Initialize database connection and model
$pdo = getDBConnection();
$donationModel = new Donation($pdo);

// Get action from request
$action = $_GET['action'] ?? 'list';

// Initialize response variables
$pageTitle = 'Donation Management';
$error = '';
$success = '';
$data = [];

// Route requests to appropriate actions
switch ($action) {
    case 'list':
        handleListAction();
        break;
    case 'create':
        handleCreateAction();
        break;
    case 'view':
        handleViewAction();
        break;
    case 'edit':
        handleEditAction();
        break;
    case 'delete':
        handleDeleteAction();
        break;
    case 'report':
        handleReportAction();
        break;
    case 'top-donors':
        handleTopDonorsAction();
        break;
    default:
        handleListAction();
}

/**
 * HZ-DON-CTRL-002: List all donations
 * Displays paginated list with filtering and search options
 */
function handleListAction() {
    global $donationModel, $pageTitle, $error, $success;

    $pageTitle = 'Donation List';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $searchTerm = $_GET['search'] ?? '';
    $typeFilter = $_GET['type'] ?? '';

    // Get donation data
    if (!empty($searchTerm)) {
        $donationData = $donationModel->searchDonations($searchTerm);
        $result = [
            'data' => $donationData,
            'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_records' => count($donationData)]
        ];
    } else if (!empty($typeFilter)) {
        $donationData = $donationModel->getDonationsByType($typeFilter);
        $result = [
            'data' => $donationData,
            'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_records' => count($donationData)]
        ];
    } else {
        $result = $donationModel->getAllDonations($page);
    }

    // Get summary statistics
    $summary = $donationModel->getDonationSummary();

    // Load view
    $donations = $result['data'] ?? [];
    $pagination = $result['pagination'] ?? [];
    include __DIR__ . "/../views/donations/list.php";
}

/**
 * HZ-DON-CTRL-003: Create new donation record
 * Handles GET (form display) and POST (form submission)
 */
function handleCreateAction() {
    global $donationModel, $pageTitle, $error, $success;

    $pageTitle = 'Record New Donation';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // HZ-DON-CTRL-003a: Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            // Validate required fields
            if (empty($_POST['DonorName']) || empty($_POST['DonationType']) || empty($_POST['DonationDate'])) {
                $error = 'Please fill in all required fields.';
            } else if ($_POST['DonationType'] === 'cash' && (empty($_POST['Amount']) || (float)$_POST['Amount'] < 0)) {
                $error = 'For cash donations, please enter a valid amount.';
            } else {
                // Create donation
                $result = $donationModel->createDonation($_POST);
                if ($result['success']) {
                    $success = $result['message'];
                    // Redirect to list
                    header('Location: DonationController.php?action=list');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
    }

    // Load create view
    include __DIR__ . "/../views/donations/create.php";
}

/**
 * HZ-DON-CTRL-004: View specific donation
 * Displays detailed donation information
 */
function handleViewAction() {
    global $donationModel, $pageTitle, $error;

    if (empty($_GET['id'])) {
        $error = 'Invalid donation ID.';
        handleListAction();
        return;
    }

    $donation = $donationModel->getDonationById((int)$_GET['id']);
    if (!$donation) {
        $error = 'Donation not found.';
        handleListAction();
        return;
    }

    $pageTitle = 'Donation Details';
    include __DIR__ . "/../views/donations/view.php";
}

/**
 * HZ-DON-CTRL-005: Edit donation record
 * Handles GET (form display) and POST (form submission)
 */
function handleEditAction() {
    global $donationModel, $pageTitle, $error, $success;

    if (empty($_GET['id'])) {
        $error = 'Invalid donation ID.';
        handleListAction();
        return;
    }

    $donation = $donationModel->getDonationById((int)$_GET['id']);
    if (!$donation) {
        $error = 'Donation not found.';
        handleListAction();
        return;
    }

    $pageTitle = 'Edit Donation';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            // Validate required fields
            if (empty($_POST['DonorName']) || empty($_POST['DonationType']) || empty($_POST['DonationDate'])) {
                $error = 'Please fill in all required fields.';
            } else if ($_POST['DonationType'] === 'cash' && (empty($_POST['Amount']) || (float)$_POST['Amount'] < 0)) {
                $error = 'For cash donations, please enter a valid amount.';
            } else {
                // Update donation
                $result = $donationModel->updateDonation((int)$_GET['id'], $_POST);
                if ($result['success']) {
                    $success = $result['message'];
                    // Refresh donation data
                    $donation = $donationModel->getDonationById((int)$_GET['id']);
                } else {
                    $error = $result['message'];
                }
            }
        }
    }

    // Load edit view
    include __DIR__ . "/../views/donations/edit.php";
}

/**
 * HZ-DON-CTRL-006: Delete donation record
 * Requires admin role for security
 */
function handleDeleteAction() {
    global $donationModel, $error, $success;

    // HZ-DON-CTRL-006a: Check admin authorization
    if ($GLOBALS['currentUser']['role'] !== 'admin') {
        $error = 'You do not have permission to delete donations.';
        handleListAction();
        return;
    }

    if (empty($_GET['id'])) {
        $error = 'Invalid donation ID.';
        handleListAction();
        return;
    }

    $donation = $donationModel->getDonationById((int)$_GET['id']);
    if (!$donation) {
        $error = 'Donation not found.';
        handleListAction();
        return;
    }

    // Confirm deletion with POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            $result = $donationModel->deleteDonation((int)$_GET['id']);
            if ($result['success']) {
                $success = $result['message'];
                header('Location: DonationController.php?action=list');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }

    // Show delete confirmation view
    include __DIR__ . "/../views/donations/delete.php";
}

/**
 * HZ-DON-CTRL-007: Generate donation reports
 * Shows statistics and analytics for donations
 */
function handleReportAction() {
    global $donationModel, $pageTitle;

    $pageTitle = 'Donation Report';

    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // Get report data
    $summary = $donationModel->getDonationSummary();
    $donationsByRange = $donationModel->getDonationsByDateRange($startDate, $endDate);
    $topDonors = $donationModel->getTopDonors(10);
    $cashTotal = $donationModel->getCashTotalByPeriod($startDate, $endDate);

    // Get donations by type breakdown
    $cashDonations = $donationModel->getDonationsByType('cash');
    $foodDonations = $donationModel->getDonationsByType('food');
    $suppliesDonations = $donationModel->getDonationsByType('supplies');
    $otherDonations = $donationModel->getDonationsByType('other');

    include __DIR__ . "/../views/donations/report.php";
}

/**
 * HZ-DON-CTRL-008: Display top donors
 * Shows most generous donors for appreciation and recognition
 */
function handleTopDonorsAction() {
    global $donationModel, $pageTitle;

    $pageTitle = 'Top Donors';
    $topDonors = $donationModel->getTopDonors(20);

    include __DIR__ . "/../views/donations/top_donors.php";
}
