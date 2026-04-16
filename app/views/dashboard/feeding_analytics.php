<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feeding Program Analytics - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            position: relative;
            height: 350px;
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
            <h1><i class="fas fa-utensils"></i> Feeding Program Analytics</h1>
            <p class="mb-0 mt-2">Attendance trends and program statistics</p>
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
                    <a href="DashboardController.php?action=feeding" class="active">
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
                            <div><i class="fas fa-calendar"></i> Today's Attendance</div>
                            <div class="stat-value"><?php echo (int)$feedingStats['today_attendance']; ?></div>
                            <small class="text-muted">beneficiaries served</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-calendar-week"></i> This Week</div>
                            <div class="stat-value"><?php echo (int)$feedingStats['weekly_attendance']; ?></div>
                            <small class="text-muted">total attendance</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-calendar-check"></i> This Month</div>
                            <div class="stat-value"><?php echo (int)$feedingStats['monthly_attendance']; ?></div>
                            <small class="text-muted">total attendance</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-user-friends"></i> Active Beneficiaries</div>
                            <div class="stat-value"><?php echo (int)$systemStats['active_beneficiaries']; ?></div>
                            <small class="text-muted">registered</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="mb-3">Attendance by Role Distribution</h5>
                    <canvas id="roleChart"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h5 class="mb-3"><i class="fas fa-chart-pie"></i> Participation Rate</h5>
                    <div style="text-align: center; padding: 20px;">
                        <div style="font-size: 3rem; font-weight: 700; color: #667eea; margin: 20px 0;">
                            <?php 
                                $activeB = (int)$systemStats['active_beneficiaries'];
                                $todayA = (int)$feedingStats['today_attendance'];
                                $rate = $activeB > 0 ? round(($todayA / $activeB) * 100, 1) : 0;
                                echo $rate;
                            ?>%
                        </div>
                        <p class="text-muted">of active beneficiaries attended today</p>
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
    <script>
        const roleData = <?php echo json_encode($attendanceByRole); ?>;
        const roleLabels = roleData.map(r => r.Role);
        const roleCounts = roleData.map(r => r.count);

        const roleCtx = document.getElementById('roleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'bar',
            data: {
                labels: roleLabels,
                datasets: [{
                    label: 'Attendance Count',
                    data: roleCounts,
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
