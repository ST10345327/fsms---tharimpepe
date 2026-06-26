<?php
function handleDashboard() {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new Dashboard($db);

    $data = [
        'system_stats' => $model->getSystemStats(),
        'feeding_stats' => $model->getFeedingStats(),
        'food_stock_status' => $model->getFoodStockStatus(),
        'food_stock_items' => $model->getFoodStockItems(),
        'donation_stats' => $model->getDonationStats(),
        'scheduling_stats' => $model->getSchedulingStats(),
        'kpis' => $model->getKPIs(),
        'weekly_attendance_trend' => $model->getWeeklyAttendanceTrend(),
        'recent_activities' => $model->getRecentActivities(10),
        'top_donors' => $model->getTopDonors(5),
    ];

    apiJsonResponse(true, 'Dashboard data retrieved', $data);
}
