<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Schedules';
$pageSubtitle = 'Mobile';
$activeNav = 'dashboard';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Schedule statistics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Volunteers</div>
      <div class="kpi-value">24</div>
      <div class="kpi-meta">Active</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
  </div>
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Today</div>
      <div class="kpi-value">8</div>
      <div class="kpi-meta">Scheduled</div>
    </div>
    <span class="kpi-icon green"><i class="fas fa-clock" aria-hidden="true"></i></span>
  </div>
</section>

<div class="proto-card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/controllers/VolunteerScheduleController.php?action=create" class="quick-action navy">
      <i class="fas fa-plus" aria-hidden="true"></i>
      <span>Add Shift</span>
    </a>
    <a href="/controllers/VolunteerController.php?action=list" class="quick-action green">
      <i class="fas fa-users" aria-hidden="true"></i>
      <span>Volunteers</span>
    </a>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>