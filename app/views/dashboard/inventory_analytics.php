<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Analytics - FSMS</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #667eea;
            margin: 10px 0;
        }
        .alert-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 4px solid #fd7e14;
        }
        .analytics-nav {
            background: white;
            border-radius: 10px;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .analytics-nav a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .analytics-nav a:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
        }
        .analytics-nav a.active {
            background: #f0f4ff;
            border-left-color: #667eea;
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-boxes"></i> Inventory Analytics</h1>
            <p class="mb-0 mt-2">Food stock management and inventory status</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Analytics Navigation -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="analytics-nav">
                    <a href="DashboardController.php?action=overview">
                        <i class="fas fa-chart-line"></i> Overview
                    </a>
                    <a href="DashboardController.php?action=feeding">
                        <i class="fas fa-utensils"></i> Feeding Program
                    </a>
                    <a href="DashboardController.php?action=volunteers">
                        <i class="fas fa-users"></i> Volunteer Performance
                    </a>
                    <a href="DashboardController.php?action=donations">
                        <i class="fas fa-gift"></i> Donations
                    </a>
                    <a href="DashboardController.php?action=inventory" class="active">
                        <i class="fas fa-boxes"></i> Inventory
                    </a>
                    <a href="DashboardController.php?action=kpis">
                        <i class="fas fa-tachometer-alt"></i> KPIs
                    </a>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="col-md-9">
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-box"></i> Items in Stock</div>
                            <div class="stat-value"><?php echo (int)$foodStockStatus['items_in_stock']; ?></div>
                            <small class="text-muted">different items</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card" style="border-left-color: #fd7e14;">
                            <div><i class="fas fa-exclamation-triangle"></i> Low Stock</div>
                            <div class="stat-value" style="color: #fd7e14;"><?php echo (int)$foodStockStatus['low_stock_items']; ?></div>
                            <small class="text-muted">need reorder</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card" style="border-left-color: #dc3545;">
                            <div><i class="fas fa-times-circle"></i> Expired Items</div>
                            <div class="stat-value" style="color: #dc3545;"><?php echo (int)$foodStockStatus['expired_items']; ?></div>
                            <small class="text-muted">must remove</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-money-bill"></i> Stock Value</div>
                            <div class="stat-value">ZWL<?php echo number_format($foodStockStatus['total_stock_value'], 0); ?></div>
                            <small class="text-muted">total inventory</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        <?php if ($foodStockStatus['low_stock_items'] > 0): ?>
            <div class="alert-card">
                <h5 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h5>
                <p class="mb-0">You have <strong><?php echo (int)$foodStockStatus['low_stock_items']; ?></strong> items below reorder level. 
                    <a href="../../controllers/FoodStockController.php?action=low_stock" class="link-warning">View details →</a>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($foodStockStatus['expired_items'] > 0): ?>
            <div class="alert-card" style="border-left-color: #dc3545;">
                <h5 style="color: #dc3545;"><i class="fas fa-times-circle"></i> Expired Items Alert</h5>
                <p class="mb-0">You have <strong><?php echo (int)$foodStockStatus['expired_items']; ?></strong> expired items in inventory. 
                    <a href="../../controllers/FoodStockController.php?action=expired" style="color: #dc3545;">View details →</a>
                </p>
            </div>
        <?php endif; ?>

        <!-- Quick Access -->
        <div class="row mt-4">
            <div class="col-md-6">
                <a href="../../controllers/FoodStockController.php?action=list" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-boxes"></i> Manage Inventory
                </a>
            </div>
            <div class="col-md-6">
                <a href="DashboardController.php?action=overview" class="btn btn-outline-primary btn-lg w-100">
                    <i class="fas fa-arrow-left"></i> Back to Overview
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
