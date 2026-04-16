<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Summary Report - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
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
        .stat-card.income { border-left-color: #28a745; }
        .stat-card.expense { border-left-color: #dc3545; }
        .stat-card.net { border-left-color: #667eea; }
        .table-card {
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
            <h1><i class="fas fa-chart-bar"></i> Financial Summary Report</h1>
            <p class="mb-0 mt-2">Monthly financial overview and analysis</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Month Selector -->
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="financial_summary">
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <select class="form-control" name="year" onchange="this.form.submit()">
                        <?php 
                            $currentYear = date('Y');
                            for ($y = $currentYear - 5; $y <= $currentYear; $y++): 
                        ?>
                            <option value="<?php echo $y; ?>" <?php echo ($_GET['year'] ?? $currentYear) == $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <select class="form-control" name="month" onchange="this.form.submit()">
                        <?php 
                            $currentMonth = date('m');
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            for ($m = 1; $m <= 12; $m++): 
                        ?>
                            <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($_GET['month'] ?? $currentMonth) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                <?php echo $months[$m - 1]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Financial Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card income">
                    <h6 class="text-muted mb-3"><i class="fas fa-arrow-up"></i> Total Income</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #28a745;">
                        ZWL<?php echo number_format((float)($financialData['total_income'] ?? 0), 2); ?>
                    </div>
                    <div class="text-muted">Donations received</div>
                    <hr>
                    <small class="text-muted">
                        Donation Records: <strong><?php echo number_format($financialData['donation_count'] ?? 0); ?></strong><br>
                        Unique Donors: <strong><?php echo number_format($financialData['unique_donors'] ?? 0); ?></strong><br>
                        Average: <strong>ZWL<?php echo number_format(($financialData['total_income'] ?? 0) / max(($financialData['donation_count'] ?? 1), 1), 2); ?></strong>
                    </small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card expense">
                    <h6 class="text-muted mb-3"><i class="fas fa-arrow-down"></i> Total Expenses</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #dc3545;">
                        ZWL<?php echo number_format((float)($financialData['total_expenses'] ?? 0), 2); ?>
                    </div>
                    <div class="text-muted">Distribution costs</div>
                    <hr>
                    <small class="text-muted">
                        Food Distributions: <strong><?php echo number_format($financialData['distribution_count'] ?? 0); ?></strong><br>
                        Average per Distribution: <strong>ZWL<?php echo number_format(($financialData['total_expenses'] ?? 0) / max(($financialData['distribution_count'] ?? 1), 1), 2); ?></strong>
                    </small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card net">
                    <h6 class="text-muted mb-3"><i class="fas fa-balance-scale"></i> Net Balance</h6>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #667eea;">
                        ZWL<?php echo number_format((float)(($financialData['total_income'] ?? 0) - ($financialData['total_expenses'] ?? 0)), 2); ?>
                    </div>
                    <div class="text-muted">Income minus expenses</div>
                    <hr>
                    <small class="text-muted">
                        Balance Status: 
                        <?php 
                            $balance = ($financialData['total_income'] ?? 0) - ($financialData['total_expenses'] ?? 0);
                            if ($balance > 0) {
                                echo '<span class="badge bg-success">Surplus</span>';
                            } else if ($balance < 0) {
                                echo '<span class="badge bg-danger">Deficit</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Balanced</span>';
                            }
                        ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Program Statistics -->
        <div class="table-card">
            <h5 class="mb-3">Program Impact Summary</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Total Beneficiaries Served</strong></td>
                        <td><?php echo number_format($financialData['total_beneficiaries'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Meals Distributed</strong></td>
                        <td><?php echo number_format($financialData['total_meals'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Volunteer Hours</strong></td>
                        <td><?php echo number_format((float)($financialData['total_volunteer_hours'] ?? 0), 1); ?> hours</td>
                    </tr>
                    <tr>
                        <td><strong>Cost per Meal</strong></td>
                        <td>ZWL<?php echo number_format(($financialData['total_expenses'] ?? 0) / max(($financialData['total_meals'] ?? 1), 1), 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Cost per Beneficiary</strong></td>
                        <td>ZWL<?php echo number_format(($financialData['total_expenses'] ?? 0) / max(($financialData['total_beneficiaries'] ?? 1), 1), 2); ?></td>
                    </tr>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</body>
</html>
