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
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stock-item {
            border-left: 4px solid #667eea;
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .stock-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .stock-item.low-stock { border-left-color: #ffc107; background: #fffbf0; }
        .stock-item.expired { border-left-color: #dc3545; background: #fff5f5; }
        .stock-item.expiring-soon { border-left-color: #fd7e14; background: #fffaf0; }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .item-name { font-size: 1.2rem; font-weight: 600; color: #333; }
        .quantity-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        .quantity-badge.low { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
        .quantity-badge.expired { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-item { padding: 10px; background: #f8f9fa; border-radius: 6px; }
        .detail-label { font-size: 0.9rem; color: #666; font-weight: 500; }
        .detail-value { font-size: 1.1rem; color: #333; font-weight: 600; margin-top: 5px; }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-sm { padding: 6px 12px; font-size: 0.9rem; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .alert-container { margin-bottom: 20px; }
        .stock-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .search-box { margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-boxes"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p class="mb-0 mt-2">Manage food inventory and stock levels</p>
                </div>
                <a href="FoodStockController.php?action=create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus"></i> Add New Item
                </a>
            </div>
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

        <!-- Statistics -->
        <div class="stock-stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($stockItems); ?></div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo count($lowStockItems); ?></div>
                <div class="stat-label">Low Stock</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo array_sum(array_map(fn($item) => $item['Quantity'], $stockItems)); ?></div>
                <div class="stat-label">Total Quantity</div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="content-card search-box">
            <form method="GET" action="FoodStockController.php" class="row g-2">
                <input type="hidden" name="action" value="list">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by item name or unit..." 
                           value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="FoodStockController.php?action=list" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stock Alerts -->
        <?php if (!empty($lowStockItems)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong><?php echo count($lowStockItems); ?> item(s) with low stock</strong> - Please review and reorder as needed.
                <a href="FoodStockController.php?action=low-stock" class="alert-link">View Details</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Food Stock Items -->
        <div class="content-card">
            <h4 class="mb-4"><i class="fas fa-list"></i> Food Stock Items</h4>

            <?php if (!empty($stockItems)): ?>
                <?php foreach ($stockItems as $item): ?>
                    <?php
                    $currentDate = new DateTime();
                    $expiryDate = $item['ExpiryDate'] ? new DateTime($item['ExpiryDate']) : null;
                    $daysUntilExpiry = $expiryDate ? $currentDate->diff($expiryDate)->days : null;
                    $isExpired = $expiryDate && $currentDate > $expiryDate;
                    $isLowStock = (int)$item['Quantity'] <= 5;
                    $isExpiring = $expiryDate && $daysUntilExpiry <= 7 && !$isExpired;

                    $itemClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring-soon' : ($isLowStock ? 'low-stock' : ''));
                    ?>
                    <div class="stock-item <?php echo $itemClass; ?>">
                        <div class="item-header">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['ItemName']); ?></div>
                                <small class="text-muted">Added: <?php echo date('M d, Y', strtotime($item['CreatedAt'])); ?></small>
                            </div>
                            <span class="quantity-badge <?php echo $isLowStock ? 'low' : ($isExpired ? 'expired' : ''); ?>">
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
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger ms-2">Expired</span>
                                        <?php elseif ($isExpiring): ?>
                                            <span class="badge bg-warning ms-2"><?php echo $daysUntilExpiry; ?> days</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em class="text-muted">No expiry date</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($item['UpdatedAt'])); ?></div>
                            </div>
                            <?php if (!empty($item['Notes'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Notes</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($item['Notes']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <a href="FoodStockController.php?action=view&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="FoodStockController.php?action=edit&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="FoodStockController.php?action=distribute&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-handshake"></i> Distribute
                            </a>
                            <a href="FoodStockController.php?action=delete&id=<?php echo (int)$item['FoodStockID']; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">No food stock items found. <a href="FoodStockController.php?action=create">Add your first item</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                            <a class="page-link" href="FoodStockController.php?action=list&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="content-card text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3" style="color: #ffc107;"></i>
                    <h5>Low Stock Items</h5>
                    <p class="text-muted">Items that need reordering</p>
                    <a href="FoodStockController.php?action=low-stock" class="btn btn-warning">
                        <i class="fas fa-list"></i> View Low Stock
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="content-card text-center">
                    <i class="fas fa-skull-crossbones fa-2x mb-3" style="color: #dc3545;"></i>
                    <h5>Expired Items</h5>
                    <p class="text-muted">Items past their expiry date</p>
                    <a href="FoodStockController.php?action=expired" class="btn btn-danger">
                        <i class="fas fa-list"></i> View Expired
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
