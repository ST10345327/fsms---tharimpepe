<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Food Item - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            border-bottom: 2px solid #28a745;
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
            border-color: #28a745;
            box-shadow: none;
        }
        .required::after {
            content: ' *';
            color: #dc3545;
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
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
        .stock-info {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .quantity-display {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-handshake"></i> Distribute Food Item</h1>
            <p class="mb-0 mt-2">Record food distribution from your inventory</p>
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

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <!-- HZ-FOOD-UI-009: Current Stock Information -->
            <div class="stock-info">
                <h5 class="mb-3"><?php echo htmlspecialchars($stockItem['ItemName']); ?></h5>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Current Stock</small>
                        <div class="quantity-display"><?php echo (int)$stockItem['Quantity']; ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($stockItem['Unit']); ?></small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Unit Type</small>
                        <div class="quantity-display" style="font-size: 1.5rem; color: #667eea;">
                            <?php echo htmlspecialchars($stockItem['Unit']); ?>
                        </div>
                        <small class="text-muted">Per distribution</small>
                    </div>
                </div>
            </div>

            <form method="POST" action="FoodStockController.php?action=distribute&id=<?php echo (int)$stockItem['FoodStockID']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- HZ-FOOD-UI-010: Distribution Form -->
                <div class="form-section">
                    <h5><i class="fas fa-boxes"></i> Distribution Details</h5>

                    <div class="form-group">
                        <label for="quantity" class="form-label required">Quantity to Distribute</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               placeholder="0" required min="1" max="<?php echo (int)$stockItem['Quantity']; ?>" step="1">
                        <div class="helper-text">
                            Enter the amount to distribute. Maximum available: <strong><?php echo (int)$stockItem['Quantity']; ?> <?php echo htmlspecialchars($stockItem['Unit']); ?></strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Item Name</label>
                        <div class="form-control-plaintext">
                            <strong><?php echo htmlspecialchars($stockItem['ItemName']); ?></strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <div class="form-control-plaintext">
                            <?php if ($stockItem['ExpiryDate']): ?>
                                <strong><?php echo date('M d, Y', strtotime($stockItem['ExpiryDate'])); ?></strong>
                                <?php if ($stockItem['expiry_status'] === 'expired'): ?>
                                    <span class="badge bg-danger ms-2">EXPIRED</span>
                                <?php elseif ($stockItem['expiry_status'] === 'expiring_soon'): ?>
                                    <span class="badge bg-warning ms-2"><?php echo $stockItem['days_until_expiry']; ?> days left</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <em class="text-muted">No expiry date</em>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Distribution Summary -->
                <div class="form-section">
                    <h5><i class="fas fa-calculator"></i> Distribution Summary</h5>
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <small>Current Stock</small>
                                <div><strong><?php echo (int)$stockItem['Quantity']; ?> <?php echo htmlspecialchars($stockItem['Unit']); ?></strong></div>
                            </div>
                            <div class="col-md-6">
                                <small>Remaining After Distribution</small>
                                <div>
                                    <strong id="remaining-quantity">
                                        <span id="remaining-value"><?php echo (int)$stockItem['Quantity']; ?></span> 
                                        <?php echo htmlspecialchars($stockItem['Unit']); ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="button-group mt-4">
                    <a href="FoodStockController.php?action=view&id=<?php echo (int)$stockItem['FoodStockID']; ?>" class="btn btn-secondary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Confirm Distribution
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // HZ-FOOD-JS-001: Real-time remaining quantity calculation
        const quantityInput = document.getElementById('quantity');
        const remainingValue = document.getElementById('remaining-value');
        const currentStock = <?php echo (int)$stockItem['Quantity']; ?>;

        quantityInput.addEventListener('input', function() {
            const distributed = Math.max(0, parseInt(this.value) || 0);
            const remaining = Math.max(0, currentStock - distributed);
            remainingValue.textContent = remaining;

            // Change color based on remaining quantity
            if (remaining <= 5 && remaining > 0) {
                remainingValue.style.color = '#ffc107';
            } else if (remaining === 0) {
                remainingValue.style.color = '#dc3545';
            } else {
                remainingValue.style.color = '#28a745';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const quantity = parseInt(quantityInput.value) || 0;
            if (quantity <= 0) {
                alert('Please enter a valid quantity to distribute.');
                e.preventDefault();
            } else if (quantity > currentStock) {
                alert('Cannot distribute more than available stock (' + currentStock + ').');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
