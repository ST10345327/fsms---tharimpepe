<?php
require_once __DIR__ . '/../../helpers/SessionHandler.php';
requireLogin();

$pageTitle = 'Food Stock';
$pageSubtitle = 'Mobile';
$activeNav = 'stock';
$user = getCurrentUser();
?>

<?php include __DIR__ . '/../includes/mobile-layout-start.php'; ?>

<section class="kpi-grid" aria-label="Stock statistics">
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Items</div>
      <div class="kpi-value">42</div>
      <div class="kpi-meta">In stock</div>
    </div>
    <span class="kpi-icon blue"><i class="fas fa-boxes-stacked" aria-hidden="true"></i></span>
  </div>
  <div class="kpi-card">
    <div>
      <div class="kpi-label">Low Stock</div>
      <div class="kpi-value">2</div>
      <div class="kpi-meta">Need restock</div>
    </div>
    <span class="kpi-icon orange"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></span>
  </div>
</section>

<div class="proto-card">
  <h2>Stock Status</h2>
  <?php $stockRows = [
    ['label' => 'Rice', 'value' => 45, 'bar' => 'bg-success', 'danger' => false],
    ['label' => 'Beans', 'value' => 78, 'bar' => 'bg-success', 'danger' => false],
    ['label' => 'Oil', 'value' => 25, 'bar' => 'bg-danger', 'danger' => true],
    ['label' => 'Maize Meal', 'value' => 82, 'bar' => 'bg-success', 'danger' => false],
  ]; ?>
  <?php foreach ($stockRows as $row): ?>
    <div class="stock-row">
      <div class="stock-line">
        <span><?php echo htmlspecialchars($row['label']); ?></span>
        <span class="<?php echo $row['danger'] ? 'danger' : ''; ?>">
          <?php echo (int)$row['value']; ?>%
        </span>
      </div>
      <div class="progress" role="progressbar" aria-label="<?php echo htmlspecialchars($row['label']); ?> stock level" aria-valuenow="<?php echo (int)$row['value']; ?>" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar <?php echo htmlspecialchars($row['bar']); ?>" style="width: <?php echo (int)$row['value']; ?>%"></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="proto-card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/controllers/FoodStockController.php?action=create" class="quick-action navy">
      <i class="fas fa-plus" aria-hidden="true"></i>
      <span>Add Stock</span>
    </a>
    <a href="/controllers/FoodStockController.php?action=distribute" class="quick-action green">
      <i class="fas fa-truck" aria-hidden="true"></i>
      <span>Distribute</span>
    </a>
  </div>
</div>

<?php include __DIR__ . '/../includes/mobile-layout-end.php'; ?>