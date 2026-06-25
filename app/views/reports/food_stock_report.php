<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Stock Report - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .alert-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .alert-low { background: #fff3cd; color: #856404; }
        .alert-expired { background: #f8d7da; color: #721c24; }
        .alert-ok { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-boxes"></i> Food Stock Report</h1>
            <p class="mb-0 mt-2">Current inventory status and stock management</p>
        </div>
    </div>

    <!-- Main Content -->
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
                        <?php echo count(array_filter($foodStockData ?? [], fn($s) => ($s['Status'] ?? 'ok') === 'low_stock')); ?>
                    </div>
                    <div class="text-muted">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #dc3545; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count(array_filter($foodStockData ?? [], fn($s) => ($s['Status'] ?? '') === 'expired')); ?>
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
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Days Until Expiry</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($foodStockData)): ?>
                            <?php foreach ($foodStockData as $item): ?>
                                <?php
                                    $status = match ($item['Status'] ?? 'ok') {
                                        'expired' => '<span class="alert-badge alert-expired">Expired</span>',
                                        'low_stock' => '<span class="alert-badge alert-low">Low Stock</span>',
                                        default => '<span class="alert-badge alert-ok">OK</span>',
                                    };
                                    $daysUntilExpiry = isset($item['days_until_expiry']) ? (int)$item['days_until_expiry'] : null;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['ItemName']); ?></strong></td>
                                    <td><?php echo (int)$item['Quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($item['Unit'] ?? '-'); ?></td>
                                    <td><?php echo $item['ExpiryDate'] ? date('M d, Y', strtotime($item['ExpiryDate'])) : '-'; ?></td>
                                    <td>
                                        <?php if ($daysUntilExpiry !== null): ?>
                                            <?php if ($daysUntilExpiry < 0): ?>
                                                <span class="text-danger">Expired</span>
                                            <?php elseif ($daysUntilExpiry <= 7): ?>
                                                <span class="text-warning"><?php echo $daysUntilExpiry; ?> days</span>
                                            <?php else: ?>
                                                <?php echo $daysUntilExpiry; ?> days
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $status; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No stock data found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between">
            <div class="d-flex gap-2">
                <a href="ReportsController.php?action=export&report=food_stock" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="ReportsController.php?action=export_xls&report=food_stock" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i> XLS
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
            <a href="ReportsController.php?action=dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
