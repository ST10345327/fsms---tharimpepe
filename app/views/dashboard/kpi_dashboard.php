<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI Dashboard - FSMS</title>
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
        .kpi-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: center;
            border-top: 4px solid #667eea;
        }
        .kpi-value {
            font-size: 3rem;
            font-weight: 700;
            margin: 15px 0;
            color: #667eea;
        }
        .kpi-label {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .kpi-unit {
            color: #999;
            font-size: 0.9rem;
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
        .kpi-green { border-top-color: #28a745; }
        .kpi-green .kpi-value { color: #28a745; }
        .kpi-orange { border-top-color: #fd7e14; }
        .kpi-orange .kpi-value { color: #fd7e14; }
        .kpi-blue { border-top-color: #17a2b8; }
        .kpi-blue .kpi-value { color: #17a2b8; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-tachometer-alt"></i> Key Performance Indicators</h1>
            <p class="mb-0 mt-2">System-wide performance metrics and goals</p>
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
                    <a href="DashboardController.php?action=inventory">
                        <i class="fas fa-boxes"></i> Inventory
                    </a>
                    <a href="DashboardController.php?action=kpis" class="active">
                        <i class="fas fa-tachometer-alt"></i> KPIs
                    </a>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="col-md-9">
                <div class="row">
                    <!-- Average Attendance Per Session -->
                    <div class="col-sm-6 col-lg-4">
                        <div class="kpi-card kpi-blue">
                            <div class="kpi-label">Average Attendance Per Session</div>
                            <div class="kpi-value"><?php echo $kpis['avg_attendance_per_session']; ?></div>
                            <div class="kpi-unit">beneficiaries per session (30-day avg)</div>
                        </div>
                    </div>

                    <!-- Volunteer Utilization Rate -->
                    <div class="col-sm-6 col-lg-4">
                        <div class="kpi-card kpi-green">
                            <div class="kpi-label">Volunteer Utilization Rate</div>
                            <div class="kpi-value"><?php echo $kpis['volunteer_utilization_rate']; ?><span style="font-size: 1.2rem;">%</span></div>
                            <div class="kpi-unit">schedules completed successfully</div>
                        </div>
                    </div>

                    <!-- Daily Donation Trend -->
                    <div class="col-sm-6 col-lg-4">
                        <div class="kpi-card <?php echo $kpis['daily_donation_trend_percent'] >= 0 ? 'kpi-green' : 'kpi-orange'; ?>">
                            <div class="kpi-label">Daily Donation Trend</div>
                            <div class="kpi-value">
                                <?php echo abs($kpis['daily_donation_trend_percent']); ?><span style="font-size: 1.2rem;">%</span>
                                <?php echo $kpis['daily_donation_trend_percent'] >= 0 ? '↑' : '↓'; ?>
                            </div>
                            <div class="kpi-unit">compared to previous day</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Metrics -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h4 class="mb-4"><i class="fas fa-chart-bar"></i> Program Statistics</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">Feeding Program</h6>
                                <p class="mb-1"><strong>This Week:</strong> <?php echo (int)$feedingStats['weekly_attendance']; ?> beneficiaries served</p>
                                <p class="mb-1"><strong>This Month:</strong> <?php echo (int)$feedingStats['monthly_attendance']; ?> beneficiaries served</p>
                                <p class="mb-0"><strong>Active Beneficiaries:</strong> <?php echo (int)$systemStats['active_beneficiaries']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">Volunteer Program</h6>
                                <p class="mb-1"><strong>Active Volunteers:</strong> <?php echo (int)$systemStats['active_volunteers']; ?></p>
                                <p class="mb-1"><strong>Upcoming Shifts:</strong> <?php echo (int)$schedulingStats['upcoming_shifts']; ?> (next 7 days)</p>
                                <p class="mb-0"><strong>Completed This Month:</strong> <?php echo (int)$schedulingStats['completed_schedules']; ?> shifts</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">Donation Program</h6>
                                <p class="mb-1"><strong>Monthly Donations:</strong> <?php echo (int)$donationStats['monthly_donations_count']; ?> contributions</p>
                                <p class="mb-1"><strong>Monthly Amount:</strong> ZWL<?php echo number_format($donationStats['monthly_donations_amount'], 0); ?></p>
                                <p class="mb-0"><strong>Total Donors:</strong> <?php echo (int)$donationStats['total_donors']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">System Users</h6>
                                <p class="mb-1"><strong>Active Users:</strong> <?php echo (int)$systemStats['system_users']; ?></p>
                                <p class="mb-1"><strong>Volunteer Hours (Month):</strong> <?php echo number_format($schedulingStats['volunteer_hours_month'], 1); ?> hours</p>
                                <p class="mb-0"><strong>Inventory Value:</strong> ZWL<?php echo number_format((float)$foodStockStatus['total_stock_value'], 0); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="DashboardController.php?action=overview" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
