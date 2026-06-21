<?php
$pageTitle = 'FSMS';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-plus-circle"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="mb-0 mt-2">Add a new food item to your inventory</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <form method="POST" action="FoodStockController.php?action=create">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- HZ-FOOD-UI-001: Basic Stock Information Section -->
                <div class="form-section">
                    <h5><i class="fas fa-info-circle"></i> Basic Information</h5>

                    <div class="form-group">
                        <label for="ItemName" class="form-label required">Item Name</label>
                        <input type="text" class="form-control" id="ItemName" name="ItemName" 
                               placeholder="e.g., Rice, Beans, Flour, Sugar" required maxlength="150"
                               value="<?php echo htmlspecialchars($_POST['ItemName'] ?? ''); ?>">
                        <div class="helper-text">Enter the name of the food item you want to add to stock</div>
                    </div>

                    <div class="form-group">
                        <label for="Unit" class="form-label required">Unit of Measurement</label>
                        <select class="form-select" id="Unit" name="Unit" required>
                            <option value="">-- Select Unit --</option>
                            <option value="kg" <?php echo ($_POST['Unit'] ?? '') === 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
                            <option value="g" <?php echo ($_POST['Unit'] ?? '') === 'g' ? 'selected' : ''; ?>>Grams (g)</option>
                            <option value="liters" <?php echo ($_POST['Unit'] ?? '') === 'liters' ? 'selected' : ''; ?>>Liters (L)</option>
                            <option value="ml" <?php echo ($_POST['Unit'] ?? '') === 'ml' ? 'selected' : ''; ?>>Milliliters (ml)</option>
                            <option value="pieces" <?php echo ($_POST['Unit'] ?? '') === 'pieces' ? 'selected' : ''; ?>>Pieces</option>
                            <option value="boxes" <?php echo ($_POST['Unit'] ?? '') === 'boxes' ? 'selected' : ''; ?>>Boxes</option>
                            <option value="bags" <?php echo ($_POST['Unit'] ?? '') === 'bags' ? 'selected' : ''; ?>>Bags</option>
                            <option value="crates" <?php echo ($_POST['Unit'] ?? '') === 'crates' ? 'selected' : ''; ?>>Crates</option>
                            <option value="other" <?php echo ($_POST['Unit'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <div class="helper-text">Choose the measurement unit for this item</div>
                    </div>

                    <div class="form-group">
                        <label for="Quantity" class="form-label required">Initial Quantity</label>
                        <input type="number" class="form-control" id="Quantity" name="Quantity" 
                               placeholder="0" required min="0" step="1"
                               value="<?php echo htmlspecialchars($_POST['Quantity'] ?? ''); ?>">
                        <div class="helper-text">Enter the starting quantity for this item</div>
                    </div>
                </div>

                <!-- HZ-FOOD-UI-002: Expiry & Storage Section -->
                <div class="form-section">
                    <h5><i class="fas fa-calendar"></i> Expiry & Storage</h5>

                    <div class="form-group">
                        <label for="ExpiryDate" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="ExpiryDate" name="ExpiryDate" 
                               value="<?php echo htmlspecialchars($_POST['ExpiryDate'] ?? ''); ?>">
                        <div class="helper-text">Leave blank if item has no expiry date. Items expiring within 7 days will be flagged</div>
                    </div>
                </div>

                <!-- HZ-FOOD-UI-003: Additional Information Section -->
                <div class="form-section">
                    <h5><i class="fas fa-sticky-note"></i> Additional Information</h5>

                    <div class="form-group">
                        <label for="Notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="Notes" name="Notes" rows="3" 
                                  placeholder="e.g., Stored in cool place, Special handling instructions" 
                                  maxlength="500"><?php echo htmlspecialchars($_POST['Notes'] ?? ''); ?></textarea>
                        <div class="helper-text">Add any additional notes about this food item (storage instructions, supplier info, etc.)</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="button-group mt-4">
                    <a href="FoodStockController.php?action=list" class="btn btn-secondary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Food Item
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
