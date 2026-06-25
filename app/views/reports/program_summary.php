<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Summary Report - FSMS</title>
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
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid;
            margin-bottom: 20px;
        }
        .stat-card.feeding { border-left-color: #667eea; }
        .stat-card.volunteer { border-left-color: #764ba2; }
        .stat-card.donation { border-left-color: #4ecdc4; }
        .stat-card.inventory { border-left-color: #ff6b6b; }
        .stat-card.beneficiaries { border-left-color: #20c997; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-chart-pie"></i> Program Summary Report</h1>
            <p class="mb-0 mt-2">Comprehensive cross-module program performance overview</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Summary Text -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-calendar-alt"></i> Report Period: 
            <?php 
                echo !empty($_GET['from_date']) ? date('M d, Y', strtotime($_GET['from_date'])) : 'Start';
                echo ' to ';
                echo !empty($_GET['to_date']) ? date('M d, Y', strtotime($_GET['to_date'])) : 'Today';
            ?>
        </div>

        <!-- Beneficiary Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card beneficiaries">
                    <h6 class="text-muted mb-3"><i class="fas fa-users"></i> Beneficiaries</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #20c997;">
                        <?php echo number_format($summaryData['beneficiaries']['total'] ?? 0); ?>
                    </div>
                    <div class="text-muted">Total Registered</div>
                    <hr>
                    <small class="text-muted">
                        Active: <strong><?php echo number_format($summaryData['beneficiaries']['active'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>

            <!-- Feeding Program Stats -->
            <div class="col-md-6">
                <div class="stat-card feeding">
                    <h6 class="text-muted mb-3"><i class="fas fa-utensils"></i> Feeding Program</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #667eea;">
                        <?php echo number_format($summaryData['attendance']['total'] ?? 0); ?>
                    </div>
                    <div class="text-muted">Total Attendance Records</div>
                    <hr>
                    <small class="text-muted">
                        Unique Beneficiaries: <strong><?php echo number_format($summaryData['attendance']['unique_beneficiaries'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Donation Program Stats -->
            <div class="col-md-6">
                <div class="stat-card donation">
                    <h6 class="text-muted mb-3"><i class="fas fa-hand-holding-heart"></i> Donation Program</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #4ecdc4;">
                        ZWL<?php echo number_format((float)($summaryData['donations']['total_amount'] ?? 0), 2); ?>
                    </div>
                    <div class="text-muted">Total Cash Donations</div>
                    <hr>
                    <small class="text-muted">
                        Total Records: <strong><?php echo number_format($summaryData['donations']['total_donations'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>

            <!-- Inventory Stats -->
            <div class="col-md-6">
                <div class="stat-card inventory">
                    <h6 class="text-muted mb-3"><i class="fas fa-warehouse"></i> Inventory Management</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #ff6b6b;">
                        <?php echo number_format($summaryData['inventory']['total_items'] ?? 0); ?>
                    </div>
                    <div class="text-muted">Total Stock Items</div>
                    <hr>
                    <small class="text-muted">
                        Total Quantity: <strong><?php echo number_format($summaryData['inventory']['total_quantity'] ?? 0); ?></strong><br>
                        Volunteer Shifts: <strong><?php echo number_format($summaryData['volunteers']['total_shifts'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between mt-5">
            <div class="d-flex gap-2">
                <a href="ReportsController.php?action=export&report=program_summary&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="ReportsController.php?action=export_xls&report=program_summary&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i> XLS
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i> Print Report
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
