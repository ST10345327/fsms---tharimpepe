<?php
$pageTitle = 'Delete Food Item';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-trash"></i> Delete Food Item</h1>
            <p class="mb-0 mt-2">Confirm deletion of this food stock item</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="confirm-card">
            <!-- HZ-FOOD-UI-011: Delete Confirmation -->
            <div class="danger-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <div class="text-center-custom">
                <h3 class="mb-3">Confirm Deletion</h3>
                <p class="text-muted">Are you sure you want to delete this food stock item? This action cannot be undone.</p>
            </div>

            <div class="item-info">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Item:</strong>
                        <p><?php echo htmlspecialchars($stockItem['ItemName']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Quantity:</strong>
                        <p><?php echo (int)$stockItem['Quantity']; ?> <?php echo htmlspecialchars($stockItem['Unit']); ?></p>
                    </div>
                </div>
                <?php if ($stockItem['ExpiryDate']): ?>
                    <div class="row">
                        <div class="col-12">
                            <strong>Expiry Date:</strong>
                            <p><?php echo date('M d, Y', strtotime($stockItem['ExpiryDate'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Warning:</strong> Deleting this item will permanently remove it from your inventory system. Consider archiving or marking as disposed instead of deleting if you need to keep records.
            </div>

            <form method="POST" action="FoodStockController.php?action=delete&id=<?php echo (int)$stockItem['FoodStockID']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="button-group">
                    <a href="FoodStockController.php?action=view&id=<?php echo (int)$stockItem['FoodStockID']; ?>" class="btn btn-secondary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-danger" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-trash"></i> Delete Item
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
