<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - FSMS</title>
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
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-clipboard-check"></i> Attendance Report</h1>
            <p class="mb-0 mt-2">View and analyze attendance records</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Attendance</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="attendance">
                
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="fas fa-filter"></i> Filter Report
                    </button>
                    <a href="ReportsController.php?action=attendance" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Report Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count($attendanceData ?? []); ?>
                    </div>
                    <div class="text-muted">Total Records</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count(array_unique(array_column($attendanceData ?? [], 'BeneficiaryID'))); ?>
                    </div>
                    <div class="text-muted">Unique Beneficiaries</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count(array_filter($attendanceData ?? [], fn($a) => $a['MealProvided'] == 'yes')); ?>
                    </div>
                    <div class="text-muted">Meals Provided</div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-card">
            <h5 class="mb-3">Attendance Records</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Beneficiary Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Meal Provided</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendanceData)): ?>
                            <?php foreach ($attendanceData as $record): ?>
                                <tr>
                                    <td><strong><?php echo date('M d, Y', strtotime($record['AttendanceDate'])); ?></strong></td>
                                    <td><?php echo htmlspecialchars($record['FullName']); ?></td>
                                    <td><?php echo htmlspecialchars($record['Role']); ?></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($record['Status']); ?></span></td>
                                    <td><?php echo $record['MealProvided'] == 'yes' ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'; ?></td>
                                    <td><?php echo htmlspecialchars($record['Notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No attendance records found</td>
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
