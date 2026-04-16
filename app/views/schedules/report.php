<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Report - FSMS</title>
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
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin: 10px 0;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-scheduled { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-chart-bar"></i> Schedule Report</h1>
            <p class="mb-0 mt-2">Analyze volunteer scheduling and hours</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar fa-2x mb-2" style="color: #667eea;"></i>
                    <div class="text-muted small">Total Schedules</div>
                    <div class="stat-value"><?php echo $stats['total_schedules'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle fa-2x mb-2" style="color: #28a745;"></i>
                    <div class="text-muted small">Completed</div>
                    <div class="stat-value"><?php echo $stats['completed'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar-check fa-2x mb-2" style="color: #17a2b8;"></i>
                    <div class="text-muted small">Scheduled</div>
                    <div class="stat-value"><?php echo $stats['scheduled'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x mb-2" style="color: #fd7e14;"></i>
                    <div class="text-muted small">Total Hours</div>
                    <div class="stat-value"><?php echo number_format($stats['total_hours'] ?? 0, 1); ?></div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Report</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="report">
                
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="scheduled" <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Volunteer Hours Summary -->
        <div class="table-card">
            <h5 class="mb-3"><i class="fas fa-users"></i> Volunteer Hours Summary</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Volunteer</th>
                            <th>Total Schedules</th>
                            <th>Completed</th>
                            <th>Hours Worked</th>
                            <th>Average Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($volunteerStats)): ?>
                            <?php foreach ($volunteerStats as $vol): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($vol['FullName']); ?></strong></td>
                                    <td><?php echo (int)$vol['total_schedules']; ?></td>
                                    <td><?php echo (int)$vol['completed']; ?></td>
                                    <td><?php echo number_format((float)$vol['total_hours'], 1); ?></td>
                                    <td><?php echo number_format((float)($vol['total_hours'] / max($vol['completed'], 1)), 1); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Schedules -->
        <div class="table-card">
            <h5 class="mb-3"><i class="fas fa-list"></i> Recent Schedules</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Volunteer</th>
                            <th>Role</th>
                            <th>Location</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentSchedules)): ?>
                            <?php foreach ($recentSchedules as $schedule): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($schedule['ScheduleDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['FullName']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Role']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Location']); ?></td>
                                    <td><?php echo substr($schedule['StartTime'], 0, 5) . ' - ' . substr($schedule['EndTime'], 0, 5); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($schedule['Status']); ?>">
                                            <?php echo ucfirst($schedule['Status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $schedule['HoursWorked'] ? number_format((float)$schedule['HoursWorked'], 1) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No schedules found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Options -->
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="VolunteerScheduleController.php?action=list" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Schedules
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
