<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Donations';
$pageSubtitle = 'Mobile';
$activeNav = 'dashboard';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Donation statistics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Total</div>
      <div class="kpi-value">R24k</div>
      <div class="kpi-meta">This month</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-hand-holding-heart" aria-hidden="true"></i></span>
  </div>
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Donors</div>
      <div class="kpi-value">18</div>
      <div class="kpi-meta">Active donors</div>
    </div>
    <span class="kpi-icon green"><i class="fas fa-users" aria-hidden="true"></i></span>
  </div>
</section>

<div class="proto-card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/controllers/DonationController.php?action=create" class="quick-action navy">
      <i class="fas fa-plus" aria-hidden="true"></i>
      <span>Add Donation</span>
    </a>
    <a href="/controllers/DonationController.php?action=report" class="quick-action green">
      <i class="fas fa-chart-bar" aria-hidden="true"></i>
      <span>Reports</span>
    </a>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>