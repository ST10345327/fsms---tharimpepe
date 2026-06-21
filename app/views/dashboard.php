<?php
$pageTitle = 'Dashboard';
try {
    require_once __DIR__ . "/../helpers/SessionHandler.php";
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../models/Dashboard.php";
    require_once __DIR__ . "/../models/FoodStock.php";
    require_once __DIR__ . "/../models/Attendance.php";
    requireLogin();

    $user = getCurrentUser();
    $isDemoMode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'];

    if ($isDemoMode) {
        $stats = $feedingStats = $schedulingStats = $donationStats = $foodStockStatus = [];
        $stockSummary = ['low_stock_count' => 0, 'expired_count' => 0];
        $lowStockItems = $recentActivities = $topDonors = $beneficiaryTrend = $attendanceByRole = $todaySummary = [];
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $dashboard = new Dashboard($db);
        $stats = $dashboard->getSystemStats();
        $feedingStats = $dashboard->getFeedingStats();
        $schedulingStats = $dashboard->getSchedulingStats();
        $donationStats = $dashboard->getDonationStats();
        $foodStockStatus = $dashboard->getFoodStockStatus();
        $stockSummary = (new FoodStock($db))->getStockSummary();
        $lowStockItems = (new FoodStock($db))->getLowStockItems();
        $recentActivities = $dashboard->getRecentActivities(8);
        $topDonors = $dashboard->getTopDonors(5);
        $beneficiaryTrend = $dashboard->getBeneficiaryTrend();
        $attendanceByRole = $dashboard->getAttendanceByRole();
        $attendanceModel = new Attendance($db);
        $todaySummary = $attendanceModel->getDailyAttendanceSummary(date('Y-m-d'));
    }
} catch (Exception $e) {
    $stats = []; $feedingStats = []; $schedulingStats = []; $donationStats = [];
    $foodStockStatus = []; $stockSummary = null; $lowStockItems = [];
    $recentActivities = []; $topDonors = []; $beneficiaryTrend = [];
    $attendanceByRole = []; $todaySummary = []; $user = null;
    error_log("Dashboard init error: " . $e->getMessage());
}
require_once __DIR__ . "/includes/layout-header.php";
?>

<div class="fsms-grid-4">
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Total Beneficiaries</div>
            <div class="fsms-stat-value"><?php echo isset($stats['total_beneficiaries']) ? (int)$stats['total_beneficiaries'] : 0; ?></div>
            <div class="fsms-stat-meta"><?php echo isset($stats['active_beneficiaries']) ? (int)$stats['active_beneficiaries'] : 0; ?> active</div>
        </div>
        <span class="fsms-stat-icon blue"><i class="fas fa-users"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Meals Served Today</div>
            <div class="fsms-stat-value"><?php echo isset($feedingStats['today_attendance']) ? (int)$feedingStats['today_attendance'] : 0; ?></div>
            <div class="fsms-stat-meta"><?php echo date('M d, Y'); ?></div>
        </div>
        <span class="fsms-stat-icon green"><i class="fas fa-utensils"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Scheduled Shifts</div>
            <div class="fsms-stat-value"><?php echo isset($schedulingStats['today_shifts']) ? (int)$schedulingStats['today_shifts'] : 0; ?></div>
            <div class="fsms-stat-meta">today</div>
        </div>
        <span class="fsms-stat-icon purple"><i class="fas fa-user-check"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Low Stock Alerts</div>
            <div class="fsms-stat-value"><?php echo isset($stockSummary['low_stock_count']) ? (int)$stockSummary['low_stock_count'] : (isset($foodStockStatus['low_stock_items']) ? (int)$foodStockStatus['low_stock_items'] : 0); ?></div>
            <div class="fsms-stat-meta"><?php echo isset($stockSummary['expired_count']) ? (int)$stockSummary['expired_count'] : (isset($foodStockStatus['expired_items']) ? (int)$foodStockStatus['expired_items'] : 0); ?> expired</div>
        </div>
        <span class="fsms-stat-icon orange"><i class="fas fa-triangle-exclamation"></i></span>
    </div>
</div>

<div class="fsms-grid-2">
    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Weekly Attendance</h2>
            <div class="fsms-chart-frame">
                <span class="chart-y y160">160</span>
                <span class="chart-y y120">120</span>
                <span class="chart-y y80">80</span>
                <span class="chart-y y40">40</span>
                <span class="chart-y y0">0</span>
                <div class="fsms-chart-grid"></div>
                <div class="fsms-chart-labels">
                    <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                </div>
            </div>
        </div>
    </div>

    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Quick Actions</h2>
            <div class="fsms-actions">
                <a href="../controllers/BeneficiaryController.php?action=create" class="fsms-action green"><i class="fas fa-user-plus"></i> Add Beneficiary</a>
                <a href="../controllers/AttendanceController.php?action=bulk-record" class="fsms-action navy"><i class="fas fa-clipboard-user"></i> Record Attendance</a>
                <a href="../controllers/FoodStockController.php?action=create" class="fsms-action orange"><i class="fas fa-box-open"></i> Add Stock</a>
                <a href="../controllers/ReportsController.php?action=dashboard" class="fsms-action purple"><i class="fas fa-chart-line"></i> View Reports</a>
            </div>
        </div>
    </div>
</div>

<div class="fsms-grid-1-1">
    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Food Stock Status</h2>
            <?php if (!empty($lowStockItems)): ?>
                <?php foreach (array_slice($lowStockItems, 0, 4) as $row): ?>
                    <?php $qty = isset($row['Quantity']) ? (int)$row['Quantity'] : 0; $pct = $qty > 0 ? min(100, round(($qty / 100) * 100)) : 0; $danger = $qty <= 5; ?>
                    <div style="margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                            <span><?php echo htmlspecialchars($row['ItemName']); ?></span>
                            <span style="<?php echo $danger ? 'color:var(--red);font-weight:600;' : ''; ?>"><?php echo $qty; ?> <?php echo htmlspecialchars($row['Unit'] ?? ''); ?></span>
                        </div>
                        <div style="background:#e5e7eb;border-radius:999px;height:6px;overflow:hidden;">
                            <div style="background:<?php echo $danger ? 'var(--red)' : 'var(--green)'; ?>;border-radius:999px;height:6px;width:<?php echo $pct; ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted" style="margin:0;">No stock data available</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Recent Activity</h2>
            <?php if (!empty($recentActivities)): ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <div style="display:grid;grid-template-columns:8px 1fr auto;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);align-items:start;">
                        <span style="background:var(--green);border-radius:50%;height:7px;width:7px;margin-top:6px;"></span>
                        <div>
                            <div style="font-size:13px;"><?php echo htmlspecialchars($activity['ActivityType'] ?? $activity['Action'] ?? 'Activity'); ?></div>
                            <div style="font-size:11px;color:var(--muted);"><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></div>
                        </div>
                        <span style="font-size:11px;color:var(--muted);white-space:nowrap;"><?php echo isset($activity['Timestamp']) ? date('M d, H:i', strtotime($activity['Timestamp'])) : ''; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted" style="margin:0;">No recent activity</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/layout-footer.php"; ?>