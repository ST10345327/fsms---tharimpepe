<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Dashboard.php';
require_once __DIR__ . '/../../models/FoodStock.php';
require_once __DIR__ . '/../../models/Attendance.php';
requireLogin();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Mobile';
$activeNav = 'dashboard';

$user = getCurrentUser();
$isDemoMode = isset($_SESSION['demo_mode']) && $_SESSION['demo_mode'];

if ($isDemoMode) {
    $stats = array('active_beneficiaries' => 0, 'total_beneficiaries' => 0, 'active_volunteers' => 0, 'total_volunteers' => 0, 'system_users' => 0);
    $feedingStats = array('today_attendance' => 0, 'weekly_attendance' => 0, 'monthly_attendance' => 0);
    $schedulingStats = array('total_shifts' => 0, 'today_shifts' => 0, 'upcoming_shifts' => 0, 'completed_schedules' => 0, 'volunteer_hours_month' => 0);
    $donationStats = array('total_donors' => 0, 'monthly_donations_count' => 0, 'monthly_donations_amount' => 0, 'yearly_donations' => 0);
    $foodStockStatus = array('items_in_stock' => 0, 'low_stock_items' => 0, 'expired_items' => 0, 'total_stock_value' => 0);
    $stockSummary = array('low_stock_count' => 0, 'expired_count' => 0);
    $lowStockItems = array();
    $recentActivities = array();
    $topDonors = array();
    $todaySummary = array();
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
    $todaySummary = (new Attendance($db))->getDailyAttendanceSummary(date('Y-m-d'));
}
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Key performance indicators">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Total Beneficiaries</div>
      <div class="kpi-value"><?php echo isset($stats['total_beneficiaries']) ? (int)$stats['total_beneficiaries'] : 0; ?></div>
      <div class="kpi-meta"><?php echo isset($stats['active_beneficiaries']) ? (int)$stats['active_beneficiaries'] : 0; ?> active</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-users" aria-hidden="true"></i></span>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-label">Meals Served Today</div>
      <div class="kpi-value"><?php echo isset($feedingStats['today_attendance']) ? (int)$feedingStats['today_attendance'] : 0; ?></div>
      <div class="kpi-meta"><?php echo date('M d, Y'); ?></div>
    </div>
    <span class="kpi-icon green"><i class="fas fa-utensils" aria-hidden="true"></i></span>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-label">Active Volunteers</div>
      <div class="kpi-value"><?php echo isset($schedulingStats['today_shifts']) ? (int)$schedulingStats['today_shifts'] : 0; ?></div>
      <div class="kpi-meta">scheduled shifts</div>
    </div>
    <span class="kpi-icon purple"><i class="fas fa-user-check" aria-hidden="true"></i></span>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-label">Low Stock Alerts</div>
      <div class="kpi-value"><?php echo isset($stockSummary['low_stock_count']) ? (int)$stockSummary['low_stock_count'] : (isset($foodStockStatus['low_stock_items']) ? (int)$foodStockStatus['low_stock_items'] : 0); ?></div>
      <div class="kpi-meta"><?php echo isset($stockSummary['expired_count']) ? (int)$stockSummary['expired_count'] : (isset($foodStockStatus['expired_items']) ? (int)$foodStockStatus['expired_items'] : 0); ?> expired</div>
    </div>
    <span class="kpi-icon orange"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></span>
  </div>
</section>

<section class="dashboard-mobile-grid">
  <div class="proto-card">
    <h2>Weekly Attendance</h2>
    <div class="chart-frame" aria-label="Weekly attendance chart">
      <span class="chart-y y160">160</span>
      <span class="chart-y y120">120</span>
      <span class="chart-y y80">80</span>
      <span class="chart-y y40">40</span>
      <span class="chart-y y0">0</span>
      <div class="chart-grid" aria-hidden="true"></div>
      <div class="chart-labels">
        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
      </div>
    </div>
  </div>

  <div class="proto-card">
    <h2>Quick Actions</h2>
    <div class="quick-actions">
      <a href="/controllers/BeneficiaryController.php?action=create" class="quick-action navy">
        <i class="fas fa-plus" aria-hidden="true"></i>
        <span>Add Beneficiary</span>
      </a>
      <a href="/controllers/AttendanceController.php?action=bulk-record" class="quick-action green">
        <i class="far fa-clipboard" aria-hidden="true"></i>
        <span>Record Attendance</span>
      </a>
      <a href="/controllers/FoodStockController.php?action=create" class="quick-action orange">
        <i class="fas fa-plus" aria-hidden="true"></i>
        <span>Add Stock</span>
      </a>
      <a href="/controllers/ReportsController.php?action=dashboard" class="quick-action purple">
        <i class="far fa-file-lines" aria-hidden="true"></i>
        <span>Generate Report</span>
      </a>
    </div>
  </div>
</section>

<section class="dashboard-mobile-bottom">
  <div class="proto-card">
    <h2>Food Stock Status</h2>
    <?php if (!empty($lowStockItems)): ?>
      <?php foreach (array_slice($lowStockItems, 0, 4) as $row): ?>
        <?php
          $qty = isset($row['Quantity']) ? (int)$row['Quantity'] : 0;
          $pct = $qty > 0 ? min(100, round(($qty / 100) * 100)) : 0;
          $isDanger = $qty <= 5 || (isset($row['expiry_status']) && $row['expiry_status'] === 'expired');
          $bar = $isDanger ? 'bg-danger' : 'bg-success';
        ?>
        <div class="stock-row">
          <div class="stock-line">
            <span><?php echo htmlspecialchars($row['ItemName']); ?></span>
            <span class="<?php echo $isDanger ? 'danger' : ''; ?>"><?php echo $qty; ?> <?php echo htmlspecialchars(isset($row['Unit']) ? $row['Unit'] : ''); ?></span>
          </div>
          <div class="progress" role="progressbar" aria-label="<?php echo htmlspecialchars($row['ItemName']); ?> stock level" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar <?php echo $bar; ?>" style="width: <?php echo $pct; ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No stock data available</p>
    <?php endif; ?>
  </div>

  <div class="proto-card">
    <h2>Recent Activity</h2>
    <?php if (!empty($recentActivities)): ?>
      <?php foreach ($recentActivities as $activity): ?>
        <div class="activity-item">
          <span class="activity-dot" aria-hidden="true"></span>
          <div>
            <div class="activity-title"><?php echo htmlspecialchars(isset($activity['ActivityType']) ? $activity['ActivityType'] : (isset($activity['Action']) ? $activity['Action'] : 'Activity')); ?></div>
            <div class="activity-by">by <?php echo htmlspecialchars(isset($activity['username']) ? $activity['username'] : 'System'); ?></div>
          </div>
          <div class="activity-time"><?php echo isset($activity['Timestamp']) ? date('M d, H:i', strtotime($activity['Timestamp'])) : ''; ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No recent activities</p>
    <?php endif; ?>
  </div>
</section>

<style>
  /* Mobile-specific minimal styling (kept inline for fast validation) */
  .kpi-grid{display:grid;gap:12px;margin-bottom:16px;grid-template-columns:repeat(2,minmax(0,1fr));}
  .dashboard-mobile-grid{display:grid;gap:12px;margin-bottom:16px;}
  .dashboard-mobile-bottom{display:grid;gap:12px;}
  @media (min-width:768px){
    .dashboard-mobile-grid{grid-template-columns:2fr 1fr;}
    .dashboard-mobile-bottom{grid-template-columns:1fr 1fr;}
  }
  .kpi-card{background:#fff;border:1px solid rgba(255,255,255,.12);border-radius:12px;box-shadow:0 1px 3px rgba(15,23,42,.18);padding:16px;display:flex;justify-content:space-between;min-height:108px;}
  .kpi-label{color:#1f2a44;font-size:13px;margin-bottom:6px;}
  .kpi-value{color:#071326;font-size:28px;font-weight:400;line-height:1.15;margin-bottom:4px;}
  .kpi-meta{color:#334155;font-size:12px;}
  .kpi-icon{align-items:center;border-radius:10px;color:#fff;display:inline-flex;font-size:20px;height:44px;justify-content:center;width:44px;}
  .kpi-icon.blue{background:#2563ff;}
  .kpi-icon.green{background:#00c950;}
  .kpi-icon.purple{background:#ad3df5;}
  .kpi-icon.orange{background:#ff5b00;}

  .proto-card{background:#fff;border:1px solid rgba(255,255,255,.12);border-radius:12px;box-shadow:0 1px 3px rgba(15,23,42,.18);padding:16px;}
  .proto-card h2{color:#071326;font-size:18px;font-weight:800;margin-bottom:14px;}

  .chart-frame{height:220px;position:relative;}
  .chart-grid{bottom:36px;left:56px;position:absolute;right:8px;top:8px;}
  .chart-grid::before{background:repeating-linear-gradient(to right,transparent 0,transparent calc(16.666% - 1px),#d6d9de calc(16.666% - 1px),#d6d9de 16.666%),repeating-linear-gradient(to bottom,transparent 0,transparent calc(25% - 1px),#d6d9de calc(25% - 1px),#d6d9de 25%);border-bottom:1px solid #9aa3af;border-left:1px solid #9aa3af;content:"";inset:0;position:absolute;}
  .chart-y{color:#4b5563;font-size:14px;left:24px;position:absolute;}
  .chart-y.y160{top:4px;}
  .chart-y.y120{top:48px;}
  .chart-y.y80{top:92px;}
  .chart-y.y40{top:136px;}
  .chart-y.y0{bottom:24px;}
  .chart-labels{bottom:10px;color:#4b5563;display:grid;font-size:13px;grid-template-columns:repeat(7,1fr);left:76px;position:absolute;right:10px;text-align:center;}

  .quick-actions{display:grid;gap:10px;}
  .quick-action{align-items:center;border-radius:10px;color:#fff;display:flex;font-weight:800;gap:12px;min-height:44px;padding:12px 14px;text-decoration:none;}
  .quick-action:hover{color:#fff;filter:brightness(.96);}
  .quick-action.navy{background:#1b3a5c;}
  .quick-action.green{background:#2e7d32;}
  .quick-action.orange{background:#ff5b00;}
  .quick-action.purple{background:#ad3df5;}

  .stock-row{margin-bottom:14px;}
  .stock-row:last-child{margin-bottom:0;}
  .stock-line{align-items:center;display:flex;justify-content:space-between;margin-bottom:7px;}
  .stock-line span{color:#071326;font-size:14px;}
  .stock-line .danger{color:#ff2e2e;}
  .progress{background:#e5e7eb;border-radius:999px;height:10px;}
  .progress-bar{border-radius:999px;}
  .bg-success{background:#2e7d32 !important;}
  .bg-danger{background:#dc2626 !important;}

  .activity-item{align-items:flex-start;border-bottom:1px solid #edf0f3;display:grid;gap:12px;grid-template-columns:10px 1fr auto;padding:12px 0;}
  .activity-item:last-child{border-bottom:0;}
  .activity-dot{background:#2e7d32;border-radius:999px;height:8px;margin-top:7px;width:8px;}
  .activity-title{color:#071326;font-size:14px;font-weight:800;}
  .activity-by,.activity-time{color:#475569;font-size:12px;}
</style>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>

