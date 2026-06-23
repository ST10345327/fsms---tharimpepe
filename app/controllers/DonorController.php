<?php
/**
 * Donor portal — dashboard, donation history, and profile access.
 */

require_once __DIR__ . '/../helpers/Rbac.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../models/ActivityLog.php';

requireLogin();
rbacRequireAnyPermission(['dashboard.donor', 'donations.own']);

$pdo = getDBConnection();
$donationModel = new Donation($pdo);
$action = $_GET['action'] ?? 'dashboard';
$currentUser = getCurrentUser();

switch ($action) {
    case 'dashboard':
        rbacRequirePermission('dashboard.donor');
        $summary = $donationModel->getDonorSummaryByUserId($currentUser['user_id']);
        $recentDonations = $donationModel->getDonationsByUserId($currentUser['user_id'], 1, 5);
        include __DIR__ . '/../views/dashboard/donor.php';
        break;

    case 'history':
        rbacRequirePermission('donations.own');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $result = $donationModel->getDonationsByUserId($currentUser['user_id'], $page, 20);
        $donations = $result['data'] ?? [];
        $pagination = $result['pagination'] ?? [];
        $summary = $donationModel->getDonorSummaryByUserId($currentUser['user_id']);
        $pageTitle = 'My Donation History';
        include __DIR__ . '/../views/donations/donor_history.php';
        break;

    default:
        header('Location: DonorController.php?action=dashboard');
        exit;
}
