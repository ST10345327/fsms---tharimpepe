<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Food Item - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: #dc3545;
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .confirm-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .danger-icon {
            font-size: 4rem;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }
        .item-info {
            background: #fff5f5;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .item-info strong {
            color: #dc3545;
            font-size: 1.2rem;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .text-center-custom {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-trash"></i> Delete Food Item</h1>
            <p class="mb-0 mt-2">Confirm deletion of this food stock item</p>
        </div>
    </div>

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
