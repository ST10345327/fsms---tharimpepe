<?php
/**
 * Module: Reports Controller
 * Purpose: Handle report generation and display
 * Reference: Enhanced Reports Module - Comprehensive reporting
 * Hazard ID: HZ-RPT-CTRL-*
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/Reports.php";
require_once __DIR__ . "/../models/ActivityLog.php";

// HZ-RPT-CTRL-001: Require authentication
requireLogin();

$reports = new Reports();
$action = $_GET['action'] ?? 'dashboard';

try {
    switch ($action) {
        // HZ-RPT-CTRL-002: Reports dashboard/menu
        case 'dashboard':
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Accessed Reports Dashboard");
            require __DIR__ . "/../views/reports/dashboard.php";
            break;

        // HZ-RPT-CTRL-003: Attendance report
        case 'attendance':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $beneficiaryId = $_GET['beneficiary_id'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Attendance Report (From: $fromDate, To: $toDate)");
            $attendanceData = $reports->getAttendanceReport($fromDate, $toDate, $beneficiaryId);
            
            require __DIR__ . "/../views/reports/attendance_report.php";
            break;

        // HZ-RPT-CTRL-004: Donation report
        case 'donations':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $donationType = $_GET['donation_type'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Donation Report (From: $fromDate, To: $toDate, Type: $donationType)");
            $donationData = $reports->getDonationReport($fromDate, $toDate, $donationType);
            $donorSummary = $reports->getDonorSummaryReport();
            
            require __DIR__ . "/../views/reports/donation_report.php";
            break;

        // HZ-RPT-CTRL-005: Volunteer performance report
        case 'volunteer_performance':
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Volunteer Performance Report");
            $volunteerData = $reports->getVolunteerPerformanceReport();
            
            require __DIR__ . "/../views/reports/volunteer_performance_report.php";
            break;

        // HZ-RPT-CTRL-006: Volunteer schedule report
        case 'volunteer_schedule':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $status = $_GET['status'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Volunteer Schedule Report (From: $fromDate, To: $toDate, Status: $status)");
            $scheduleData = $reports->getVolunteerScheduleReport($fromDate, $toDate, $status);
            
            require __DIR__ . "/../views/reports/volunteer_schedule_report.php";
            break;

        // HZ-RPT-CTRL-007: Food stock report
        case 'food_stock':
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Food Stock Report");
            $foodStockData = $reports->getFoodStockReport();
            
            require __DIR__ . "/../views/reports/food_stock_report.php";
            break;

        // HZ-RPT-CTRL-008: Food distribution report
        case 'food_distribution':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Food Distribution Report (From: $fromDate, To: $toDate)");
            $distributionData = $reports->getFoodDistributionReport($fromDate, $toDate);
            
            require __DIR__ . "/../views/reports/food_distribution_report.php";
            break;

        // HZ-RPT-CTRL-009: Beneficiary report
        case 'beneficiaries':
            $roleFilter = $_GET['role'] ?? null;
            $statusFilter = $_GET['status'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Beneficiary Report (Role: $roleFilter, Status: $statusFilter)");
            $beneficiaryData = $reports->getBeneficiaryReport($roleFilter, $statusFilter);
            
            require __DIR__ . "/../views/reports/beneficiary_report.php";
            break;

        // HZ-RPT-CTRL-010: Activity audit report
        case 'audit':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            $userId = $_GET['user_id'] ?? null;
            $activityType = $_GET['activity_type'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Activity Audit Report (From: $fromDate, To: $toDate, User: $userId, Type: $activityType)");
            $auditData = $reports->getActivityAuditReport($fromDate, $toDate, $userId, $activityType);
            
            require __DIR__ . "/../views/reports/audit_report.php";
            break;

        // HZ-RPT-CTRL-011: Program summary report
        case 'program_summary':
            $fromDate = $_GET['from_date'] ?? null;
            $toDate = $_GET['to_date'] ?? null;
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Program Summary Report (From: $fromDate, To: $toDate)");
            $summaryData = $reports->getProgramSummaryReport($fromDate, $toDate);
            
            require __DIR__ . "/../views/reports/program_summary.php";
            break;

        // HZ-RPT-CTRL-012: Financial summary report
        case 'financial_summary':
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            
            ActivityLog::log(getCurrentUser()['user_id'], 'view_report', 'Reports', 0, "Viewed Financial Summary Report (Year: $year, Month: $month)");
            $financialData = $reports->getMonthlyFinancialSummary($year, $month);
            
            require __DIR__ . "/../views/reports/financial_summary.php";
            break;

        default:
            require __DIR__ . "/../views/reports/dashboard.php";
    }
} catch (Exception $e) {
    error_log("Reports Error: " . $e->getMessage());
    $_SESSION['error'] = "Error generating report: " . $e->getMessage();
    header("Location: ../../views/dashboard.php");
    exit;
}
?>
