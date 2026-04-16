<?php
/**
 * Module: Dashboard Controller
 * Purpose: Handle dashboard requests and display system analytics
 * Reference: Dashboard Enhancements - Cross-module statistics
 * Hazard ID: HZ-DASH-CTRL-*
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/Dashboard.php";

// HZ-DASH-CTRL-001: Require authentication
requireLogin();

$dashboard = new Dashboard();
$action = $_GET['action'] ?? 'overview';

try {
    switch ($action) {
        // HZ-DASH-CTRL-002: Dashboard overview with system statistics
        case 'overview':
            $systemStats = $dashboard->getSystemStats();
            $feedingStats = $dashboard->getFeedingStats();
            $foodStockStatus = $dashboard->getFoodStockStatus();
            $donationStats = $dashboard->getDonationStats();
            $schedulingStats = $dashboard->getSchedulingStats();
            $kpis = $dashboard->getKPIs();
            $recentActivities = $dashboard->getRecentActivities(8);
            $topDonors = $dashboard->getTopDonors(5);
            $volunteerPerformance = $dashboard->getVolunteerPerformance(5);
            $beneficiaryTrend = $dashboard->getBeneficiaryTrend();
            $attendanceByRole = $dashboard->getAttendanceByRole();
            $donationSources = $dashboard->getDonationSources();
            
            require __DIR__ . "/../views/dashboard/overview.php";
            break;

        // HZ-DASH-CTRL-003: Feeding program analytics
        case 'feeding':
            $feedingStats = $dashboard->getFeedingStats();
            $beneficiaryTrend = $dashboard->getBeneficiaryTrend();
            $attendanceByRole = $dashboard->getAttendanceByRole();
            $systemStats = $dashboard->getSystemStats();
            
            require __DIR__ . "/../views/dashboard/feeding_analytics.php";
            break;

        // HZ-DASH-CTRL-004: Volunteer analytics
        case 'volunteers':
            $schedulingStats = $dashboard->getSchedulingStats();
            $volunteerPerformance = $dashboard->getVolunteerPerformance(20);
            $systemStats = $dashboard->getSystemStats();
            
            require __DIR__ . "/../views/dashboard/volunteer_analytics.php";
            break;

        // HZ-DASH-CTRL-005: Donation analytics
        case 'donations':
            $donationStats = $dashboard->getDonationStats();
            $topDonors = $dashboard->getTopDonors(10);
            $donationSources = $dashboard->getDonationSources();
            
            require __DIR__ . "/../views/dashboard/donation_analytics.php";
            break;

        // HZ-DASH-CTRL-006: Inventory analytics
        case 'inventory':
            $foodStockStatus = $dashboard->getFoodStockStatus();
            $systemStats = $dashboard->getSystemStats();
            
            require __DIR__ . "/../views/dashboard/inventory_analytics.php";
            break;

        // HZ-DASH-CTRL-007: KPI dashboard
        case 'kpis':
            $kpis = $dashboard->getKPIs();
            $feedingStats = $dashboard->getFeedingStats();
            $schedulingStats = $dashboard->getSchedulingStats();
            $donationStats = $dashboard->getDonationStats();
            $systemStats = $dashboard->getSystemStats();
            
            require __DIR__ . "/../views/dashboard/kpi_dashboard.php";
            break;

        default:
            $systemStats = $dashboard->getSystemStats();
            $feedingStats = $dashboard->getFeedingStats();
            $foodStockStatus = $dashboard->getFoodStockStatus();
            $donationStats = $dashboard->getDonationStats();
            $schedulingStats = $dashboard->getSchedulingStats();
            $kpis = $dashboard->getKPIs();
            $recentActivities = $dashboard->getRecentActivities(8);
            $topDonors = $dashboard->getTopDonors(5);
            $volunteerPerformance = $dashboard->getVolunteerPerformance(5);
            $beneficiaryTrend = $dashboard->getBeneficiaryTrend();
            $attendanceByRole = $dashboard->getAttendanceByRole();
            $donationSources = $dashboard->getDonationSources();
            
            require __DIR__ . "/../views/dashboard/overview.php";
    }
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard: " . $e->getMessage();
    header("Location: ../../views/dashboard.php");
    exit;
}
?>
