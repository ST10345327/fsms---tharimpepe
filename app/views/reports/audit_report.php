<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Audit Report - FSMS</title>
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
        .activity-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-clipboard-list"></i> Activity Audit Report</h1>
            <p class="mb-0 mt-2">User activity log and system operations audit trail</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Audit Records</h6>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="audit">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id'] ?? ''); ?>" placeholder="Username or ID">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Activity Type</label>
                    <select class="form-control" name="activity_type">
                        <option value="">All Activities</option>
                        <option value="Create" <?php echo ($_GET['activity_type'] ?? '') === 'Create' ? 'selected' : ''; ?>>Create</option>
                        <option value="Update" <?php echo ($_GET['activity_type'] ?? '') === 'Update' ? 'selected' : ''; ?>>Update</option>
                        <option value="Delete" <?php echo ($_GET['activity_type'] ?? '') === 'Delete' ? 'selected' : ''; ?>>Delete</option>
                        <option value="Login" <?php echo ($_GET['activity_type'] ?? '') === 'Login' ? 'selected' : ''; ?>>Login</option>
                        <option value="Logout" <?php echo ($_GET['activity_type'] ?? '') === 'Logout' ? 'selected' : ''; ?>>Logout</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="ReportsController.php?action=audit" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Audit Table -->
        <div class="table-card">
            <h5 class="mb-3">
                Activity Log
                <small class="text-muted">(<?php echo count($auditData ?? []); ?> records)</small>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Activity Type</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($auditData)): ?>
                            <?php foreach ($auditData as $log): ?>
                                <?php 
                                    $activityType = htmlspecialchars($log['ActivityType'] ?? '');
                                    $statusBg = match($activityType) {
                                        'Create' => 'success',
                                        'Update' => 'info',
                                        'Delete' => 'danger',
                                        'Login' => 'primary',
                                        'Logout' => 'secondary',
                                        default => 'light'
                                    };
                                ?>
                                <tr>
                                    <td><small><?php echo date('M d, Y H:i:s', strtotime($log['Timestamp'] ?? '')); ?></small></td>
                                    <td><strong><?php echo htmlspecialchars($log['FullName'] ?? $log['UserName'] ?? ''); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $statusBg; ?>"><?php echo $activityType; ?></span></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($log['Module'] ?? '-'); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['Description'] ?? '-'); ?></td>
                                    <td>
                                        <i class="fas fa-check-circle text-success"></i>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No activity records found</td>
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
