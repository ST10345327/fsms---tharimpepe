<?php
/**
 * Module: Donor Controller
 * Purpose: Handle donor-specific views and donation history
 * Reference: HZ-DONOR-CTRL-001 to HZ-DONOR-CTRL-003
 * Author: WIL Student
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../helpers/bootstrap.php";

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../helpers/Rbac.php";
require_once __DIR__ . "/../models/Donation.php";
require_once __DIR__ . "/../models/ActivityLog.php";
require_once __DIR__ . "/../../config/database.php";

// Require login
requireLogin();

// HZ-DONOR-CTRL-RBAC: Enforce donor permission (donor only)
rbacRequirePermission('donations.own');

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$currentUser = getCurrentUser();

try {
    $db = getDBConnection();
    $donationModel = new Donation($db);

    switch ($action) {
        // HZ-DONOR-CTRL-001: Donor dashboard
        case 'dashboard':
            $pageTitle = 'Donor Dashboard';
            
            // Get donor's donation history
            $donorEmail = $currentUser['email'] ?? '';
            $donorName = $currentUser['username'] ?? 'Donor';
            
            // Search for donations by donor email
            $donations = [];
            if (!empty($donorEmail)) {
                $donations = $donationModel->searchDonations($donorEmail);
            }
            
            // Build summary stats for the view
            $summary = [
                'donation_count' => count($donations),
                'total_cash' => 0,
                'inkind_count' => 0,
                'last_donation' => null,
            ];
            $totalAmount = 0;
            foreach ($donations as $donation) {
                $type = strtolower((string)($donation['DonationType'] ?? ''));
                $amount = (float)($donation['Amount'] ?? 0);
                if ($type === 'cash') {
                    $summary['total_cash'] += $amount;
                } else {
                    $summary['inkind_count']++;
                }
                $date = $donation['DonationDate'] ?? null;
                if ($date && (!$summary['last_donation'] || $date > $summary['last_donation'])) {
                    $summary['last_donation'] = $date;
                }
            }
            
            $recentDonations = ['data' => array_slice($donations, 0, 5)];
            
            include __DIR__ . "/../views/dashboard/donor.php";
            break;

        // HZ-DONOR-CTRL-004: View donation receipt
        case 'receipt':
            $pageTitle = 'Donation Receipt';
            $donationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $donation = $donationModel->getDonationById($donationId);
            
            // Ensure the donation belongs to this donor
            if (!$donation || strtolower($donation['DonorEmail'] ?? '') !== strtolower($currentUser['email'] ?? '')) {
                $_SESSION['error'] = 'Donation not found or access denied.';
                header("Location: DonorController.php?action=history");
                exit();
            }
            
            include __DIR__ . "/../views/donations/receipt.php";
            break;

        // HZ-DONOR-CTRL-005: Donor impact report
        case 'impact':
            $pageTitle = 'My Impact Report';
            
            $donorEmail = $currentUser['email'] ?? '';
            $donations = [];
            
            if (!empty($donorEmail)) {
                $donations = $donationModel->searchDonations($donorEmail);
            }
            
            // Build detailed summary
            $summary = [
                'donation_count' => count($donations),
                'total_cash' => 0,
                'inkind_count' => 0,
                'last_donation' => null,
                'first_donation' => null,
            ];
            foreach ($donations as $donation) {
                $type = strtolower((string)($donation['DonationType'] ?? ''));
                $amount = (float)($donation['Amount'] ?? 0);
                if ($type === 'cash') {
                    $summary['total_cash'] += $amount;
                } else {
                    $summary['inkind_count']++;
                }
                $date = $donation['DonationDate'] ?? null;
                if ($date) {
                    if (!$summary['last_donation'] || $date > $summary['last_donation']) {
                        $summary['last_donation'] = $date;
                    }
                    if (!$summary['first_donation'] || $date < $summary['first_donation']) {
                        $summary['first_donation'] = $date;
                    }
                }
            }
            
            include __DIR__ . "/../views/donations/impact_report.php";
            break;

        // HZ-DONOR-CTRL-002: Donor donation history
        case 'history':
            $pageTitle = 'My Donation History';
            
            $donorEmail = $currentUser['email'] ?? '';
            $donations = [];
            
            if (!empty($donorEmail)) {
                $donations = $donationModel->searchDonations($donorEmail);
            }
            
            // Build summary stats for the view
            $summary = [
                'donation_count' => count($donations),
                'total_cash' => 0,
                'inkind_count' => 0,
                'last_donation' => null,
            ];
            foreach ($donations as $donation) {
                $type = strtolower((string)($donation['DonationType'] ?? ''));
                $amount = (float)($donation['Amount'] ?? 0);
                if ($type === 'cash') {
                    $summary['total_cash'] += $amount;
                } else {
                    $summary['inkind_count']++;
                }
                $date = $donation['DonationDate'] ?? null;
                if ($date && (!$summary['last_donation'] || $date > $summary['last_donation'])) {
                    $summary['last_donation'] = $date;
                }
            }
            
            include __DIR__ . "/../views/donations/donor_history.php";
            break;

        // HZ-DONOR-CTRL-003: Record new donation (for donors)
        case 'create_donation':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    $_SESSION['error'] = "Security validation failed. Please try again.";
                    header("Location: DonorController.php?action=create_donation");
                    exit();
                }
                $donorName = trim($_POST['DonorName'] ?? $currentUser['username']);
                $donorEmail = trim($_POST['DonorEmail'] ?? $currentUser['email']);
                $donationType = $_POST['DonationType'] ?? 'cash';
                $amount = $_POST['Amount'] ?? null;
                $description = trim($_POST['Description'] ?? '');
                $donationDate = $_POST['DonationDate'] ?? date('Y-m-d');

                // Validation
                if (empty($donorName) || empty($donationType) || empty($donationDate)) {
                    $_SESSION['error'] = "Please fill in all required fields";
                    header("Location: DonorController.php?action=create_donation");
                    exit();
                }

                if ($donationType === 'cash' && (empty($amount) || (float)$amount < 0)) {
                    $_SESSION['error'] = "Please enter a valid amount for cash donations";
                    header("Location: DonorController.php?action=create_donation");
                    exit();
                }

                // Create donation
                $result = $donationModel->createDonation([
                    'DonorName' => $donorName,
                    'DonorEmail' => $donorEmail,
                    'DonationType' => $donationType,
                    'Amount' => $amount,
                    'Description' => $description,
                    'DonationDate' => $donationDate
                ]);

                if ($result['success']) {
                    $donationId = $result['id'] ?? null;
                    if ($donationId) {
                        ActivityLog::log($currentUser['user_id'], 'create_donation', 'Donation', $donationId, "Donor created donation: $donorName ($donationType)");
                    }
                    $_SESSION['success'] = "Thank you! Your donation has been recorded successfully.";
                    header("Location: DonorController.php?action=history");
                    exit();
                } else {
                    $_SESSION['error'] = $result['message'];
                    header("Location: DonorController.php?action=create_donation");
                    exit();
                }
            }

            include __DIR__ . "/../views/donations/donor_create.php";
            break;

        default:
            header("Location: DonorController.php?action=dashboard");
            exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: DonorController.php?action=dashboard");
    exit();
}
?>