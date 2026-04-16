<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .form-group { margin-bottom: 20px; }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: none;
        }
        .required::after {
            content: ' *';
            color: #dc3545;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .helper-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-plus-circle"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="mb-0 mt-2">Add a new food item to your inventory</p>
        </div>
    </div>

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
