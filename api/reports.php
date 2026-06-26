<?php
function handleReports() {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Reports($db);

    $type = $_GET['type'] ?? 'summary';
    $fromDate = $_GET['from_date'] ?? null;
    $toDate = $_GET['to_date'] ?? null;

    switch ($type) {
        case 'attendance':
            $data = $model->getAttendanceReport($fromDate, $toDate);
            apiJsonResponse(true, 'Attendance report retrieved', $data);
            break;
        case 'donations':
            $donationType = $_GET['donation_type'] ?? null;
            $data = $model->getDonationReport($fromDate, $toDate, $donationType);
            apiJsonResponse(true, 'Donation report retrieved', $data);
            break;
        case 'donors':
            $data = $model->getDonorSummaryReport();
            apiJsonResponse(true, 'Donor summary report retrieved', $data);
            break;
        case 'food_stock':
            $data = $model->getFoodStockReport();
            apiJsonResponse(true, 'Food stock report retrieved', $data);
            break;
        case 'beneficiaries':
            $statusFilter = $_GET['status'] ?? null;
            $data = $model->getBeneficiaryReport($statusFilter);
            apiJsonResponse(true, 'Beneficiary report retrieved', $data);
            break;
        case 'volunteers':
            $data = $model->getVolunteerPerformanceReport();
            apiJsonResponse(true, 'Volunteer report retrieved', $data);
            break;
        case 'activity':
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
            $action = $_GET['action'] ?? null;
            $data = $model->getActivityAuditReport($fromDate, $toDate, $userId, $action);
            apiJsonResponse(true, 'Activity audit report retrieved', $data);
            break;
        case 'financial':
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            $data = $model->getMonthlyFinancialSummary($year, $month);
            apiJsonResponse(true, 'Financial summary retrieved', $data);
            break;
        case 'summary':
        default:
            $data = $model->getProgramSummaryReport($fromDate, $toDate);
            apiJsonResponse(true, 'Program summary report retrieved', $data);
            break;
    }
}
