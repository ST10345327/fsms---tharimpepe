<?php
$pageTitle = 'FSMS';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-box"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p class="mb-0 mt-2">Detailed food stock information</p>
                </div>
                <a href="FoodStockController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- HZ-FOOD-UI-008: Item Details -->
                <div class="content-card">
                    <h3 class="mb-4"><?php echo htmlspecialchars($stockItem['ItemName']); ?></h3>

                    <!-- Status Section -->
                    <div class="detail-section">
                        <h5 class="mb-3">Current Status</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Quantity Available</div>
                                <div class="detail-value">
                                    <?php echo (int)$stockItem['Quantity']; ?>
                                    <small class="text-muted ms-2"><?php echo htmlspecialchars($stockItem['Unit']); ?></small>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <?php if ((int)$stockItem['Quantity'] <= 5): ?>
                                        <span class="status-badge" style="background: #fff3cd; color: #856404;">Low Stock</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background: #d4edda; color: #155724;">OK</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiry Information -->
                    <div class="detail-section">
                        <h5 class="mb-3">Expiry Information</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Expiry Date</div>
                                <div class="detail-value">
                                    <?php if ($stockItem['ExpiryDate']): ?>
                                        <?php echo date('M d, Y', strtotime($stockItem['ExpiryDate'])); ?>
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
                            <div class="detail-item">
                                <div class="detail-label">Days Until Expiry</div>
                                <div class="detail-value">
                                    <?php if ($stockItem['ExpiryDate']): ?>
                                        <?php if ($stockItem['days_until_expiry'] < 0): ?>
                                            <span class="text-danger">Expired <?php echo abs($stockItem['days_until_expiry']); ?> days ago</span>
                                        <?php elseif ($stockItem['days_until_expiry'] <= 7): ?>
                                            <span class="text-warning"><?php echo $stockItem['days_until_expiry']; ?> days</span>
                                        <?php else: ?>
                                            <span class="text-success"><?php echo $stockItem['days_until_expiry']; ?> days</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em class="text-muted">N/A</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock History -->
                    <div class="detail-section">
                        <h5 class="mb-3">Stock History</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Added On</div>
                                <div class="detail-value"><?php echo date('M d, Y \a\t h:i A', strtotime($stockItem['CreatedAt'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value"><?php echo date('M d, Y \a\t h:i A', strtotime($stockItem['UpdatedAt'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if (!empty($stockItem['Notes'])): ?>
                        <div class="detail-section">
                            <h5 class="mb-3">Notes</h5>
                            <p><?php echo htmlspecialchars($stockItem['Notes']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons mt-4">
                        <a href="FoodStockController.php?action=edit&id=<?php echo (int)$stockItem['FoodStockID']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Item
                        </a>
                        <a href="FoodStockController.php?action=distribute&id=<?php echo (int)$stockItem['FoodStockID']; ?>" class="btn btn-success">
                            <i class="fas fa-handshake"></i> Distribute
                        </a>
                        <a href="FoodStockController.php?action=delete&id=<?php echo (int)$stockItem['FoodStockID']; ?>" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="content-card text-center">
                    <i class="fas fa-boxes fa-5x mb-3" style="color: #667eea;"></i>
                    <h5>Food Stock Item</h5>
                    <p class="text-muted">ID: <?php echo (int)$stockItem['FoodStockID']; ?></p>
                    <hr>
                    <p class="mb-0">This item was added to your inventory on <strong><?php echo date('M d, Y', strtotime($stockItem['CreatedAt'])); ?></strong></p>
                </div>

                <?php if ((int)$stockItem['Quantity'] <= 5): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <h6>Low Stock Alert</h6>
                        <p class="mb-0">This item is running low and should be reordered soon</p>
                    </div>
                <?php endif; ?>

                <?php if ($stockItem['expiry_status'] === 'expired'): ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-skull-crossbones fa-2x mb-2"></i>
                        <h6>Item Expired</h6>
                        <p class="mb-0">This item has expired and should be discarded</p>
                    </div>
                <?php elseif ($stockItem['expiry_status'] === 'expiring_soon'): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-hourglass-end fa-2x mb-2"></i>
                        <h6>Expiring Soon</h6>
                        <p class="mb-0">This item expires in <?php echo $stockItem['days_until_expiry']; ?> days</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
