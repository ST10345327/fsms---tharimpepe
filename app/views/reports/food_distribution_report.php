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
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-box"></i> Food Distribution Report</h1>
            <p class="mb-0 mt-2">Food distribution history and tracking</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Distribution Records</h6>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="food_distribution">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" placeholder="Location">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Food Item</label>
                    <input type="text" class="form-control" name="food_item" value="<?php echo htmlspecialchars($_GET['food_item'] ?? ''); ?>" placeholder="Item name">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="ReportsController.php?action=food_distribution" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Distribution Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2rem; font-weight: 700;">
                        <?php echo count($distributionData ?? []); ?>
                    </div>
                    <div class="text-muted">Total Distributions</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #764ba2; font-size: 2rem; font-weight: 700;">
                        <?php 
                            $uniqueItems = count(array_unique(array_map(fn($d) => $d['FoodItem'] ?? '', $distributionData ?? [])));
                            echo $uniqueItems;
                        ?>
                    </div>
                    <div class="text-muted">Unique Food Items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #4ecdc4; font-size: 2rem; font-weight: 700;">
                        <?php 
                            $totalQty = array_sum(array_map(fn($d) => (float)($d['QuantityDistributed'] ?? 0), $distributionData ?? []));
                            echo number_format($totalQty, 1);
                        ?>
                    </div>
                    <div class="text-muted">Total Quantity Distributed</div>
                </div>
            </div>
        </div>

        <!-- Distribution Table -->
        <div class="table-card">
            <h5 class="mb-3">Distribution Records</h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Distribution Date</th>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Purpose</th>
                            <th>Distributor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($distributionData)): ?>
                            <?php foreach ($distributionData as $dist): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($dist['DistributionDate'] ?? '')); ?></td>
                                    <td><strong><?php echo htmlspecialchars($dist['FoodItem'] ?? '-'); ?></strong></td>
                                    <td><?php echo number_format((float)($dist['QuantityDistributed'] ?? 0), 1); ?></td>
                                    <td><?php echo htmlspecialchars($dist['UnitOfMeasure'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dist['Location'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dist['Purpose'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dist['DistributedBy'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No distribution records found</td>
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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
            <h1><i class="fas fa-dolly"></i> Food Distribution Report</h1>
            <p class="mb-0 mt-2">Track food distribution history and consumption trends</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Distributions</h6>
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
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 style="color: #667eea;"><?php echo number_format(count($distributionData ?? [])); ?></h4>
                    <small class="text-muted">Total Distributions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 style="color: #4ecdc4;"><?php echo number_format($totalQuantityDistributed ?? 0, 1); ?></h4>
                    <small class="text-muted">Total Qty Distributed</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 style="color: #764ba2;">ZWL<?php echo number_format((float)($totalDistributionCost ?? 0), 2); ?></h4>
                    <small class="text-muted">Total Distribution Cost</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h4 style="color: #ff6b6b;"><?php echo count(array_unique(array_column($distributionData ?? [], 'BeneficiaryID'))); ?></h4>
                    <small class="text-muted">Unique Beneficiaries</small>
                </div>
            </div>
        </div>

        <!-- Distribution Table -->
        <div class="table-card">
            <h5 class="mb-3">Distribution Records</h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Food Item</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Beneficiary</th>
                            <th>Location</th>
                            <th>Purpose</th>
                            <th>Cost (ZWL)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($distributionData)): ?>
                            <?php foreach ($distributionData as $dist): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($dist['DistributionDate'] ?? '')); ?></td>
                                    <td><strong><?php echo htmlspecialchars($dist['FoodItem'] ?? ''); ?></strong></td>
                                    <td><?php echo number_format((float)$dist['Quantity'] ?? 0, 1); ?></td>
                                    <td><?php echo htmlspecialchars($dist['UnitOfMeasure'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dist['BeneficiaryName'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dist['Location'] ?? '-'); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($dist['Purpose'] ?? ''); ?></span></td>
                                    <td>ZWL<?php echo number_format((float)$dist['Cost'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No distribution records found</td>
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
