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
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
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
        .alert-item {
            border-left: 4px solid #ffc107;
            background: #fffbf0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .alert-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .alert-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .item-name { font-size: 1.2rem; font-weight: 600; color: #333; }
        .quantity-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
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
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border: none; color: white; }
        .btn-warning:hover { background: linear-gradient(135deg, #ffb300 0%, #e68900 100%); color: white; }
        .alert-container { margin-bottom: 20px; }
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
                    <h1><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p class="mb-0 mt-2">Items with low stock levels - please reorder soon</p>
                </div>
                <a href="FoodStockController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to All Items
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Explanation Alert -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> 
            <strong>Low Stock Threshold:</strong> Items showing less than 5 units or expiring within 7 days are displayed below.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Low Stock Items -->
        <div class="content-card">
            <h4 class="mb-4"><i class="fas fa-list"></i> Low Stock Items</h4>

            <?php if (!empty($lowStockItems)): ?>
                <?php foreach ($lowStockItems as $item): ?>
                    <div class="alert-item">
                        <div class="alert-item-header">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['ItemName']); ?></div>
                                <small class="text-muted">Added: <?php echo date('M d, Y', strtotime($item['CreatedAt'])); ?></small>
                            </div>
                            <span class="quantity-badge">
                                <?php echo (int)$item['Quantity']; ?> <?php echo htmlspecialchars($item['Unit']); ?>
                            </span>
                        </div>

                        <div class="item-details">
                            <div class="detail-item">
                                <div class="detail-label">Quantity</div>
                                <div class="detail-value"><?php echo (int)$item['Quantity']; ?> <?php echo htmlspecialchars($item['Unit']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Expiry Date</div>
                                <div class="detail-value">
                                    <?php if ($item['ExpiryDate']): ?>
                                        <?php echo date('M d, Y', strtotime($item['ExpiryDate'])); ?>
                                        <?php if ($item['days_until_expiry'] <= 7 && $item['days_until_expiry'] >= 0): ?>
                                            <span class="badge bg-warning ms-2"><?php echo $item['days_until_expiry']; ?> days</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em class="text-muted">No expiry</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Priority</div>
                                <div class="detail-value">
                                    <?php if ((int)$item['Quantity'] === 0): ?>
                                        <span class="badge bg-danger">CRITICAL</span>
                                    <?php elseif ((int)$item['Quantity'] <= 2): ?>
                                        <span class="badge bg-danger">HIGH</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">MEDIUM</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="FoodStockController.php?action=view&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="FoodStockController.php?action=edit&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                    data-bs-target="#reorderModal" 
                                    onclick="setReorderItem('<?php echo htmlspecialchars($item['ItemName']); ?>', <?php echo (int)$item['FoodStockID']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Reorder
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="text-success">All Stock Levels Healthy</h5>
                    <p class="text-muted">Congratulations! No items are currently running low on stock. All items have sufficient quantities and are within their expiry dates.</p>
                    <a href="FoodStockController.php?action=list" class="btn btn-primary mt-3">
                        <i class="fas fa-list"></i> View All Items
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistics -->
        <?php if (!empty($lowStockItems)): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="content-card text-center">
                        <h3 class="text-warning"><?php echo count($lowStockItems); ?></h3>
                        <p class="text-muted">Items with low stock</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="content-card text-center">
                        <h3 class="text-info">
                            <?php 
                            $totalLowQuantity = array_sum(array_map(fn($i) => (int)$i['Quantity'], $lowStockItems));
                            echo $totalLowQuantity;
                            ?>
                        </h3>
                        <p class="text-muted">Total quantity at risk</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reorder Modal -->
    <div class="modal fade" id="reorderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Reorder Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Item: <strong id="reorderItemName"></strong></p>
                    <input type="text" class="form-control" placeholder="Quantity to reorder" id="reorderQty">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="confirmReorder()">Confirm Reorder</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setReorderItem(itemName, itemId) {
            document.getElementById('reorderItemName').textContent = itemName;
        }

        function confirmReorder() {
            alert('Reorder functionality would integrate with your procurement system. This is a placeholder.');
        }
    </script>
</body>
</html>
