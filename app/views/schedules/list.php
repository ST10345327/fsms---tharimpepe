<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Scheduling - FSMS</title>
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
        .page-header h1 { margin: 0; font-weight: 700; }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table thead { background: #f8f9fa; }
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-calendar"></i> Volunteer Scheduling</h1>
                    <p class="mb-0 mt-2">Manage volunteer shifts and schedules</p>
                </div>
                <a href="VolunteerScheduleController.php?action=create" class="btn btn-light">
                    <i class="fas fa-plus"></i> Add Schedule
                </a>
            </div>
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

        <!-- Statistics -->
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
                    <div class="text-muted small">Scheduled</div>
                    <div class="stat-value"><?php echo $stats['scheduled'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-checkmark fa-2x mb-2" style="color: #17a2b8;"></i>
                    <div class="text-muted small">Completed</div>
                    <div class="stat-value"><?php echo $stats['completed'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-users fa-2x mb-2" style="color: #fd7e14;"></i>
                    <div class="text-muted small">Volunteers</div>
                    <div class="stat-value"><?php echo $stats['total_volunteers'] ?? 0; ?></div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Schedules</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="list">
                
                <div class="col-md-3">
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="scheduled" <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Schedules Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Volunteer</th>
                            <th>Time</th>
                            <th>Role</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($schedules)): ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><strong><?php echo date('M d, Y', strtotime($schedule['ScheduleDate'])); ?></strong></td>
                                    <td><?php echo htmlspecialchars($schedule['FullName']); ?></td>
                                    <td><?php echo substr($schedule['StartTime'], 0, 5) . ' - ' . substr($schedule['EndTime'], 0, 5); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Role']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Location']); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo strtolower($schedule['Status']); ?>">
                                            <?php echo ucfirst($schedule['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="VolunteerScheduleController.php?action=view&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="VolunteerScheduleController.php?action=edit&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="VolunteerScheduleController.php?action=delete&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No schedules found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <a href="VolunteerScheduleController.php?action=shifts" class="btn btn-outline-primary btn-lg w-100">
                    <i class="fas fa-clock"></i> Today's Shifts
                </a>
            </div>
            <div class="col-md-6">
                <a href="VolunteerScheduleController.php?action=report" class="btn btn-outline-info btn-lg w-100">
                    <i class="fas fa-chart-bar"></i> Schedule Report
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
