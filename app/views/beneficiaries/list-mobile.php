<?php
require_once __DIR__ . '/../../../public/helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Beneficiaries';
$pageSubtitle = 'Mobile';
$activeNav = 'beneficiaries';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../../views/includes/mobile-layout-start.php'; ?>

  <section class="kpi-grid" aria-label="Beneficiary statistics">
    <div class="kpi-card">
      <div>
        <div class="kpi-label">Total</div>
        <div class="kpi-value">342</div>
        <div class="kpi-meta">Registered</div>
      </div>
      <span class="kpi-icon blue"><i class="fas fa-users" aria-hidden="true"></i></span>
    </div>
    <div class="kpi-card">
      <div>
        <div class="kpi-label">Active</div>
        <div class="kpi-value">298</div>
        <div class="kpi-meta">Receiving meals</div>
      </div>
      <span class="kpi-icon green"><i class="fas fa-user-check" aria-hidden="true"></i></span>
    </div>
  </section>

  <div class="proto-card">
    <h2>Quick Actions</h2>
    <div class="quick-actions">
      <a href="/public/controllers/BeneficiaryController.php?action=create" class="quick-action navy">
        <i class="fas fa-plus" aria-hidden="true"></i>
        <span>Register</span>
      </a>
      <a href="/public/controllers/BeneficiaryController.php?action=search" class="quick-action green">
        <i class="fas fa-search" aria-hidden="true"></i>
        <span>Search</span>
      </a>
    </div>
  </div>

<?php include __DIR__ . '/../../views/includes/mobile-layout-end.php'; ?>