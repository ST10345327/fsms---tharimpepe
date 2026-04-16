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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .expired-item {
            border-left: 4px solid #dc3545;
            background: #fff5f5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .expired-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .item-name { font-size: 1.2rem; font-weight: 600; color: #333; }
        .expired-badge {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-item {
            padding: 10px;
            background: white;
            border-radius: 6px;
        }
        .detail-label { font-size: 0.9rem; color: #666; font-weight: 500; }
        .detail-value { font-size: 1.1rem; color: #333; font-weight: 600; margin-top: 5px; }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-sm { padding: 6px 12px; font-size: 0.9rem; }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .btn-danger:hover { background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%); }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-skull-crossbones"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p class="mb-0 mt-2">Items past their expiry date - must be disposed of</p>
                </div>
                <a href="FoodStockController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to All Items
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Critical Alert -->
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>CRITICAL:</strong> Food items that have passed their expiry date must be disposed of immediately for food safety and regulatory compliance.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Expired Items -->
        <div class="content-card">
            <h4 class="mb-4"><i class="fas fa-list"></i> Expired Food Items</h4>

            <?php if (!empty($expiredItems)): ?>
                <?php foreach ($expiredItems as $item): ?>
                    <div class="expired-item">
                        <div class="expired-item-header">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['ItemName']); ?></div>
                                <small class="text-muted">Added: <?php echo date('M d, Y', strtotime($item['CreatedAt'])); ?></small>
                            </div>
                            <span class="expired-badge">
                                <i class="fas fa-times-circle"></i> EXPIRED <?php echo abs((int)$item['days_overdue']); ?> DAYS AGO
                            </span>
                        </div>

                        <div class="item-details">
                            <div class="detail-item">
                                <div class="detail-label">Quantity</div>
                                <div class="detail-value"><?php echo (int)$item['Quantity']; ?> <?php echo htmlspecialchars($item['Unit']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Expiry Date</div>
                                <div class="detail-value text-danger">
                                    <strong><?php echo date('M d, Y', strtotime($item['ExpiryDate'])); ?></strong>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Days Overdue</div>
                                <div class="detail-value text-danger">
                                    <strong><?php echo abs((int)$item['days_overdue']); ?> days</strong>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($item['Notes'])): ?>
                            <div class="alert alert-sm alert-secondary mb-3">
                                <small><strong>Notes:</strong> <?php echo htmlspecialchars($item['Notes']); ?></small>
                            </div>
                        <?php endif; ?>

                        <div class="action-buttons">
                            <a href="FoodStockController.php?action=view&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="confirmDisposal(<?php echo (int)$item['FoodStockID']; ?>, '<?php echo htmlspecialchars($item['ItemName']); ?>')">
                                <i class="fas fa-trash"></i> Mark as Disposed
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="text-success">No Expired Items</h5>
                    <p class="text-muted">Excellent! No food items in your inventory have expired. Continue to monitor expiry dates to maintain food safety standards.</p>
                    <a href="FoodStockController.php?action=list" class="btn btn-primary mt-3">
                        <i class="fas fa-list"></i> View All Items
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Disposal Guidelines -->
        <?php if (!empty($expiredItems)): ?>
            <div class="content-card">
                <h5 class="mb-3"><i class="fas fa-shield-alt"></i> Disposal Guidelines</h5>
                <ul class="mb-0">
                    <li>Expired food must be disposed of immediately to prevent food safety risks</li>
                    <li>Do not distribute or use expired items under any circumstances</li>
                    <li>Ensure proper disposal following local environmental regulations</li>
                    <li>Keep records of disposed items for audit and compliance purposes</li>
                    <li>Monitor supplier quality to reduce expiration rates</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDisposal(itemId, itemName) {
            if (confirm('Confirm disposal of ' + itemName + '? This action cannot be undone.')) {
                // Redirect to delete action
                window.location.href = 'FoodStockController.php?action=delete&id=' + itemId;
            }
        }
    </script>
</body>
</html>
