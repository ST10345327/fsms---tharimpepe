<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Distribution Report - FSMS</title>
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
        .filter-form {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-box"></i> Food Distribution Report</h1>
            <p class="mb-0 mt-2">Food distribution tracking and stock management</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Stock Records</h6>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="food_distribution">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="ReportsController.php?action=food_distribution" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-box">
                    <h4 style="color: #667eea;"><?php echo number_format(count($distributionData ?? [])); ?></h4>
                    <small class="text-muted">Total Stock Items</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h4 style="color: #4ecdc4;"><?php echo number_format(array_sum(array_column($distributionData ?? [], 'current_quantity'))); ?></h4>
                    <small class="text-muted">Total Quantity in Stock</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <h4 style="color: #ff6b6b;"><?php echo count(array_filter($distributionData ?? [], fn($d) => !empty($d['ExpiryDate']) && strtotime($d['ExpiryDate']) < time())); ?></h4>
                    <small class="text-muted">Expired Items</small>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="table-card">
            <h5 class="mb-3">Food Stock Records</h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Stock Date</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($distributionData)): ?>
                            <?php foreach ($distributionData as $item): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($item['StockDate'] ?? '')); ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['ItemName'] ?? ''); ?></strong></td>
                                    <td><?php echo (int)($item['current_quantity'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($item['Unit'] ?? '-'); ?></td>
                                    <td><?php echo $item['ExpiryDate'] ? date('M d, Y', strtotime($item['ExpiryDate'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($item['Notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No stock records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between">
            <div class="d-flex gap-2">
                <a href="ReportsController.php?action=export&report=food_distribution&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="ReportsController.php?action=export_xls&report=food_distribution&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>" class="btn btn-primary">
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
