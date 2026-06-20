<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Reports';
$pageSubtitle = 'Mobile';
$activeNav = 'dashboard';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Report statistics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Generated</div>
      <div class="kpi-value">12</div>
      <div class="kpi-meta">This week</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
  </div>
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Exports</div>
      <div class="kpi-value">3</div>
      <div class="kpi-meta">Pending</div>
    </div>
    <span class="kpi-icon green"><i class="fas fa-download" aria-hidden="true"></i></span>
  </div>
</section>

<div class="proto-card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/controllers/ReportsController.php?action=dashboard" class="quick-action navy">
      <i class="fas fa-chart-bar" aria-hidden="true"></i>
      <span>Dashboard</span>
    </a>
    <a href="/controllers/ReportsController.php?action=generate" class="quick-action green">
      <i class="fas fa-file-pdf" aria-hidden="true"></i>
      <span>Generate PDF</span>
    </a>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>