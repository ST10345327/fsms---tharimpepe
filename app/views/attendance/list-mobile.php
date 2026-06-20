<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Attendance';
$pageSubtitle = 'Mobile';
$activeNav = 'attendance';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Attendance statistics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Today</div>
      <div class="kpi-value">138</div>
      <div class="kpi-meta">Meals served</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-utensils" aria-hidden="true"></i></span>
  </div>
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Rate</div>
      <div class="kpi-value">82%</div>
      <div class="kpi-meta">Attendance rate</div>
    </div>
    <span class="kpi-icon green"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
  </div>
</section>

<div class="proto-card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/controllers/AttendanceController.php?action=bulk-record" class="quick-action navy">
      <i class="far fa-clipboard" aria-hidden="true"></i>
      <span>Record</span>
    </a>
    <a href="/controllers/AttendanceController.php?action=report" class="quick-action green">
      <i class="fas fa-chart-bar" aria-hidden="true"></i>
      <span>Report</span>
    </a>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>