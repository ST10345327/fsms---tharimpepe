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
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .table-responsive { border-radius: 10px; overflow: hidden; }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        .table tbody tr:hover { background-color: #f8f9fa; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
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
                    <h1><i class="fas fa-chart-bar"></i> Food Stock Report</h1>
                    <p class="mb-0 mt-2">Inventory analytics and statistics</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filters -->
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-filter"></i> Filter Report</h5>
            <form method="GET" action="FoodStockController.php" class="filter-section">
                <input type="hidden" name="action" value="report">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Generate
                            </button>
                            <a href="FoodStockController.php?action=report" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- HZ-FOOD-UI-012: Statistics Summary -->
        <?php if ($summary): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo (int)$summary['total_items']; ?></div>
                    <div class="stat-label">Total Items in Stock</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo (int)$summary['total_quantity']; ?></div>
                    <div class="stat-label">Total Quantity</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number" style="color: #ffc107;"><?php echo (int)$summary['low_stock_count']; ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number" style="color: #dc3545;"><?php echo (int)$summary['expired_count']; ?></div>
                    <div class="stat-label">Expired Items</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detailed Report -->
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-table"></i> Inventory Details</h5>

            <?php if (!empty($allStock)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reportTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allStock as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['ItemName']); ?></strong></td>
                            <td><?php echo (int)$item['Quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['Unit']); ?></td>
                            <td>
                                <?php if ($item['ExpiryDate']): ?>
                                    <?php echo date('M d, Y', strtotime($item['ExpiryDate'])); ?>
                                <?php else: ?>
                                    <em class="text-muted">N/A</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$item['Quantity'] <= 5): ?>
                                    <span class="badge bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">OK</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($item['CreatedAt'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">No stock items found for the selected date range.</div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alert -->
        <?php if (!empty($lowStockItems)): ?>
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> Low Stock Items</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Quantity</th>
                            <th>Unit</th>
                            <th>Action Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['ItemName']); ?></strong></td>
                            <td><span class="badge bg-warning"><?php echo (int)$item['Quantity']; ?></span></td>
                            <td><?php echo htmlspecialchars($item['Unit']); ?></td>
                            <td>
                                <a href="FoodStockController.php?action=view&id=<?php echo (int)$item['FoodStockID']; ?>" 
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Expired Items -->
        <?php if (!empty($expiredItems)): ?>
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-skull-crossbones"></i> Expired Items (Must Dispose)</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Expired Date</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiredItems as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['ItemName']); ?></strong></td>
                            <td><?php echo (int)$item['Quantity']; ?> <?php echo htmlspecialchars($item['Unit']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['ExpiryDate'])); ?></td>
                            <td>
                                <span class="badge bg-danger"><?php echo abs((int)$item['days_overdue']); ?> days</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToCSV() {
            const table = document.getElementById('reportTable');
            if (!table) {
                alert('No data to export');
                return;
            }

            let csv = [];
            const rows = table.querySelectorAll('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].textContent.trim().replace(/[\n\r]/g, ' ');
                    row.push('"' + text.replace(/"/g, '""') + '"');
                }
                csv.push(row.join(','));
            }

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'food_stock_report_<?php echo date('Y-m-d'); ?>.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
