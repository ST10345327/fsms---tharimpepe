<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Performance Report - FSMS</title>
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
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-star"></i> Volunteer Performance Report</h1>
            <p class="mb-0 mt-2">Analyze volunteer performance metrics and contributions</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Performance Table -->
        <div class="table-card">
            <h5 class="mb-3">Volunteer Performance Rankings</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Total Shifts</th>
                            <th>Completed</th>
                            <th>Cancelled</th>
                            <th>No-Shows</th>
                            <th>Total Hours</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($volunteerData)): ?>
                            <?php foreach ($volunteerData as $volunteer): ?>
                                <?php 
                                    $totalShifts = (int)$volunteer['total_shifts'] ?: 1;
                                    $completionRate = round(((int)$volunteer['completed_shifts'] / $totalShifts) * 100, 1);
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($volunteer['FullName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($volunteer['Email']); ?></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($volunteer['Status']); ?></span></td>
                                    <td><?php echo (int)$volunteer['total_shifts']; ?></td>
                                    <td><span class="badge bg-success"><?php echo (int)$volunteer['completed_shifts']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo (int)$volunteer['cancelled_shifts']; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo (int)$volunteer['no_show_shifts']; ?></span></td>
                                    <td><strong><?php echo number_format((float)($volunteer['total_hours'] ?? 0), 1); ?></strong></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" style="width: <?php echo $completionRate; ?>%">
                                                <?php echo $completionRate; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No volunteer data found</td>
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
