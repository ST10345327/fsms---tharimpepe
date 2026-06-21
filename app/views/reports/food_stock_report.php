<?php
$pageTitle = 'Food Stock Report';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-boxes"></i> Food Stock Report</h1>
            <p class="mb-0 mt-2">Current inventory status and stock management</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <!-- Stock Status Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count($foodStockData ?? []); ?>
                    </div>
                    <div class="text-muted">Total Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #fd7e14; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count(array_filter($foodStockData ?? [], fn($s) => (int)$s['QuantityRemaining'] <= (int)$s['ReorderLevel'])); ?>
                    </div>
                    <div class="text-muted">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #dc3545; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count(array_filter($foodStockData ?? [], fn($s) => strtotime($s['ExpiryDate']) < time())); ?>
                    </div>
                    <div class="text-muted">Expired Items</div>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="table-card">
            <h5 class="mb-3">Food Stock Inventory</h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Remaining</th>
                            <th>Unit</th>
                            <th>Reorder Level</th>
                            <th>Expiry Date</th>
                            <th>Unit Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($foodStockData)): ?>
                            <?php foreach ($foodStockData as $item): ?>
                                <?php
                                    $remaining = (int)$item['QuantityRemaining'];
                                    $reorderLevel = (int)$item['ReorderLevel'];
                                    $expiry = strtotime($item['ExpiryDate']);
                                    
                                    if ($expiry < time()) {
                                        $status = '<span class="alert-badge alert-expired">Expired</span>';
                                    } elseif ($remaining <= $reorderLevel) {
                                        $status = '<span class="alert-badge alert-low">Low Stock</span>';
                                    } else {
                                        $status = '<span class="alert-badge alert-ok">OK</span>';
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['FoodItem']); ?></strong></td>
                                    <td><?php echo (int)$item['Quantity']; ?></td>
                                    <td><?php echo (int)$item['QuantityRemaining']; ?></td>
                                    <td><?php echo htmlspecialchars($item['UnitOfMeasure']); ?></td>
                                    <td><?php echo (int)$item['ReorderLevel']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($item['ExpiryDate'])); ?></td>
                                    <td>ZWL<?php echo number_format((float)$item['UnitCost'], 2); ?></td>
                                    <td><?php echo $status; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No stock data found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="ReportsController.php?action=dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
