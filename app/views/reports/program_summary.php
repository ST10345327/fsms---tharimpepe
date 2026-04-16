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

        <!-- Feeding Program Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card feeding">
                    <h6 class="text-muted mb-3"><i class="fas fa-utensils"></i> Feeding Program</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #667eea;">
                        <?php echo number_format($summaryData['total_attendance'] ?? 0); ?>
                    </div>
                    <div class="text-muted">Total Attendance Records</div>
                    <hr>
                    <small class="text-muted">
                        Total Meals Distributed: <strong><?php echo number_format($summaryData['total_meals_distributed'] ?? 0); ?></strong><br>
                        Unique Beneficiaries: <strong><?php echo number_format($summaryData['unique_beneficiaries'] ?? 0); ?></strong><br>
                        Days Operational: <strong><?php echo number_format($summaryData['operational_days'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>

            <!-- Volunteer Program Stats -->
            <div class="col-md-6">
                <div class="stat-card volunteer">
                    <h6 class="text-muted mb-3"><i class="fas fa-users"></i> Volunteer Program</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #764ba2;">
                        <?php echo number_format($summaryData['total_volunteer_hours'] ?? 0, 1); ?>
                    </div>
                    <div class="text-muted">Total Volunteer Hours</div>
                    <hr>
                    <small class="text-muted">
                        Active Volunteers: <strong><?php echo number_format($summaryData['active_volunteers'] ?? 0); ?></strong><br>
                        Completed Shifts: <strong><?php echo number_format($summaryData['completed_shifts'] ?? 0); ?></strong><br>
                        Completion Rate: <strong><?php echo number_format($summaryData['volunteer_completion_rate'] ?? 0, 1); ?>%</strong>
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
                        ZWL<?php echo number_format((float)($summaryData['total_donations'] ?? 0), 2); ?>
                    </div>
                    <div class="text-muted">Total Donations</div>
                    <hr>
                    <small class="text-muted">
                        Unique Donors: <strong><?php echo number_format($summaryData['unique_donors'] ?? 0); ?></strong><br>
                        Average Donation: <strong>ZWL<?php echo number_format((float)(($summaryData['total_donations'] ?? 0) / max(($summaryData['unique_donors'] ?? 1), 1)), 2); ?></strong><br>
                        Donation Frequency: <strong><?php echo number_format($summaryData['donation_frequency'] ?? 0); ?> records</strong>
                    </small>
                </div>
            </div>

            <!-- Inventory Stats -->
            <div class="col-md-6">
                <div class="stat-card inventory">
                    <h6 class="text-muted mb-3"><i class="fas fa-warehouse"></i> Inventory Management</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #ff6b6b;">
                        <?php echo number_format($summaryData['total_inventory_value'] ?? 0, 2); ?>
                    </div>
                    <div class="text-muted">Total Inventory Value (ZWL)</div>
                    <hr>
                    <small class="text-muted">
                        Stock Items: <strong><?php echo number_format($summaryData['total_stock_items'] ?? 0); ?></strong><br>
                        Low Stock Items: <strong><?php echo number_format($summaryData['low_stock_items'] ?? 0); ?></strong><br>
                        Food Items Distributed: <strong><?php echo number_format($summaryData['total_distributions'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between mt-5">
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
