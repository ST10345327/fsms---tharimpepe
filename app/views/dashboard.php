<?php
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../helpers/Rbac.php";
requireLogin();

$user = getCurrentUser();
$role = strtolower((string)($user['role'] ?? ''));
// Redirect donors to their own dashboard
if ($role === 'donor') {
    header("Location: /controllers/DonorController.php?action=dashboard");
    exit;
}
$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .dashboard-wrap {
            padding-top: 24px;
        }

        .kpi-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 24px;
        }

        .kpi-card {
            align-items: flex-start;
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
            display: flex;
            justify-content: space-between;
            min-height: 130px;
            padding: 24px;
        }

        .kpi-label {
            color: #1f2a44;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .kpi-value {
            color: #071326;
            font-size: 30px;
            font-weight: 400;
            line-height: 1.15;
            margin-bottom: 4px;
        }

        .kpi-meta {
            color: #334155;
            font-size: 12px;
        }

        .kpi-icon {
            align-items: center;
            border-radius: 10px;
            color: #fff;
            display: inline-flex;
            font-size: 22px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .kpi-icon.blue { background: #2563ff; }
        .kpi-icon.green { background: #00c950; }
        .kpi-icon.purple { background: #ad3df5; }
        .kpi-icon.orange { background: #ff5b00; }

        .dashboard-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: 2fr 1.1fr;
            margin-bottom: 24px;
        }

        .dashboard-bottom-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: 1fr 1fr;
        }

        .proto-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
            padding: 24px;
        }

        .proto-card h2 {
            color: #071326;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .chart-frame {
            height: 248px;
            position: relative;
        }

        .chart-grid {
            bottom: 36px;
            left: 64px;
            position: absolute;
            right: 8px;
            top: 8px;
        }

        .chart-grid::before {
            background:
                repeating-linear-gradient(to right, transparent 0, transparent calc(16.666% - 1px), #d6d9de calc(16.666% - 1px), #d6d9de 16.666%),
                repeating-linear-gradient(to bottom, transparent 0, transparent calc(25% - 1px), #d6d9de calc(25% - 1px), #d6d9de 25%);
            border-bottom: 1px solid #9aa3af;
            border-left: 1px solid #9aa3af;
            content: "";
            inset: 0;
            position: absolute;
        }

        .chart-y {
            color: #4b5563;
            font-size: 16px;
            left: 32px;
            position: absolute;
        }

        .chart-y.y160 { top: 4px; }
        .chart-y.y120 { top: 55px; }
        .chart-y.y80 { top: 107px; }
        .chart-y.y40 { top: 160px; }
        .chart-y.y0 { bottom: 28px; }

        .chart-labels {
            bottom: 10px;
            color: #4b5563;
            display: grid;
            font-size: 16px;
            grid-template-columns: repeat(7, 1fr);
            left: 92px;
            position: absolute;
            right: 18px;
            text-align: center;
        }

        .quick-actions {
            display: grid;
            gap: 13px;
        }

        .quick-action {
            align-items: center;
            border-radius: 9px;
            color: #fff;
            display: flex;
            font-weight: 700;
            gap: 14px;
            min-height: 48px;
            padding: 12px 18px;
            text-decoration: none;
        }

        .quick-action:hover {
            color: #fff;
            filter: brightness(0.96);
        }

        .quick-action.navy { background: #1b3a5c; }
        .quick-action.green { background: #2e7d32; }
        .quick-action.orange { background: #ff5b00; }
        .quick-action.purple { background: #ad3df5; }

        .stock-row {
            margin-bottom: 16px;
        }

        .stock-row:last-child {
            margin-bottom: 0;
        }

        .stock-line {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .stock-line span {
            color: #071326;
            font-size: 14px;
        }

        .stock-line .danger {
            color: #ff2e2e;
        }

        .progress {
            background: #e5e7eb;
            border-radius: 999px;
            height: 8px;
        }

        .progress-bar {
            border-radius: 999px;
        }

        .activity-item {
            align-items: flex-start;
            border-bottom: 1px solid #edf0f3;
            display: grid;
            gap: 12px;
            grid-template-columns: 10px 1fr auto;
            padding: 12px 0;
        }

        .activity-item:last-child {
            border-bottom: 0;
        }

        .activity-dot {
            background: #2e7d32;
            border-radius: 999px;
            height: 8px;
            margin-top: 7px;
            width: 8px;
        }

        .activity-title {
            color: #071326;
            font-size: 14px;
        }

        .activity-by,
        .activity-time {
            color: #475569;
            font-size: 12px;
        }

        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid,
            .dashboard-bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .chart-labels {
                font-size: 12px;
                left: 76px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/includes/navbar.php"; ?>

    <main class="container-fluid dashboard-wrap pb-5">
        <section class="kpi-grid" aria-label="Key performance indicators">
            <div class="kpi-card">
                <div>
                    <div class="kpi-label">Total Beneficiaries</div>
                    <div class="kpi-value"><?php echo (int)$systemStats['total_beneficiaries']; ?></div>
                    <div class="kpi-meta">active: <?php echo (int)$systemStats['active_beneficiaries']; ?></div>
                </div>
                <span class="kpi-icon blue"><i class="fas fa-users" aria-hidden="true"></i></span>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-label">Meals Served Today</div>
                    <div class="kpi-value"><?php echo (int)$feedingStats['today_attendance']; ?></div>
                    <div class="kpi-meta">As of <?php echo date('g:i A'); ?></div>
                </div>
                <span class="kpi-icon green"><i class="fas fa-utensils" aria-hidden="true"></i></span>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-label">Active Volunteers</div>
                    <div class="kpi-value"><?php echo (int)$systemStats['active_volunteers']; ?></div>
                    <div class="kpi-meta"><?php echo (int)$schedulingStats['today_shifts']; ?> scheduled today</div>
                </div>
                <span class="kpi-icon purple"><i class="fas fa-user-check" aria-hidden="true"></i></span>
            </div>

            <div class="kpi-card">
                <div>
                    <div class="kpi-label">Low Stock Alerts</div>
                    <div class="kpi-value"><?php echo (int)$foodStockStatus['low_stock_items']; ?></div>
                    <div class="kpi-meta">Requires attention</div>
                </div>
                <span class="kpi-icon orange"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
        </section>

        <section class="dashboard-grid">
            <div class="proto-card">
                <h2>Weekly Attendance</h2>
                <div class="chart-frame" aria-label="Weekly attendance chart">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>

            <div class="proto-card">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <?php if (in_array($role, ['admin', 'staff'], true)): ?>
                    <a href="../controllers/BeneficiaryController.php?action=create" class="quick-action navy">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span>Add Beneficiary</span>
                    </a>
                    <a href="../controllers/AttendanceController.php?action=bulk-record" class="quick-action green">
                        <i class="far fa-clipboard" aria-hidden="true"></i>
                        <span>Record Attendance</span>
                    </a>
                    <a href="../controllers/FoodStockController.php?action=create" class="quick-action orange">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span>Add Stock</span>
                    </a>
                    <a href="../controllers/ReportsController.php?action=dashboard" class="quick-action purple">
                        <i class="far fa-file-lines" aria-hidden="true"></i>
                        <span>Generate Report</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="dashboard-bottom-grid">
            <div class="proto-card">
                <h2>Food Stock Status</h2>
                <?php if (!empty($foodStockItems)): ?>
                    <?php foreach ($foodStockItems as $row): ?>
                        <div class="stock-row">
                            <div class="stock-line">
                                <span><?php echo htmlspecialchars($row['label']); ?> (<?php echo (int)$row['quantity']; ?> <?php echo htmlspecialchars($row['unit']); ?>)</span>
                                <span class="<?php echo $row['danger'] ? 'danger' : ''; ?>"><?php echo (int)$row['percent']; ?>%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="<?php echo htmlspecialchars($row['label']); ?> stock level" aria-valuenow="<?php echo (int)$row['percent']; ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?php echo $row['danger'] ? 'bg-danger' : 'bg-success'; ?>" style="width: <?php echo (int)$row['percent']; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No stock items recorded</p>
                <?php endif; ?>
            </div>

            <div class="proto-card">
                <h2>Recent Activity</h2>
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-dot" aria-hidden="true"></span>
                            <div>
                                <div class="activity-title"><?php echo htmlspecialchars($activity['Action']); ?></div>
                                <div class="activity-by">by <?php echo htmlspecialchars($activity['username'] ?? 'system'); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('M d, H:i', strtotime($activity['Timestamp'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No recent activity</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const weeklyData = <?php echo json_encode($weeklyChart); ?>;
    const labels = weeklyData.map(d => d.label);
    const counts = weeklyData.map(d => d.count);

    const ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Attendance',
                data: counts,
                backgroundColor: '#2563ff',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
    </script>
</body>
</html>
