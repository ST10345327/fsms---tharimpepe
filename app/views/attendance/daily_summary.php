<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Summary - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stats-card .label { color: #666; font-size: 14px; }
        .summary-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .beneficiary-row {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .beneficiary-row:last-child { border-bottom: none; }
        .beneficiary-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .beneficiary-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .beneficiary-details h6 { margin: 0; color: #333; }
        .beneficiary-details small { color: #666; }
        .status-indicator {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-not_recorded { background-color: #e2e3e5; color: #383d41; }
        .status-marked { background-color: #fff3cd; color: #856404; }
        .date-selector {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .quick-actions { margin-bottom: 30px; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-calendar-day"></i> Daily Attendance Summary</h1>
                    <p class="mb-0 mt-2">Overview of meal distribution for <?php echo date('l, F d, Y', strtotime($sessionDate)); ?></p>
                </div>
                <a href="AttendanceController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Date Selector -->
        <div class="date-selector">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" action="AttendanceController.php" class="d-flex">
                        <input type="hidden" name="action" value="daily-summary">
                        <input type="date" name="date" class="form-control me-2"
                               value="<?php echo htmlspecialchars($sessionDate); ?>" max="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> View Summary
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="AttendanceController.php?action=bulk-record&date=<?php echo $sessionDate; ?>" class="btn btn-success">
                            <i class="fas fa-users"></i> Bulk Record
                        </a>
                        <a href="AttendanceController.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Record Single
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo count($summary); ?></div>
                    <div class="label">Total Beneficiaries</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $stats['present_count']; ?></div>
                    <div class="label">Meals Distributed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $stats['absent_count']; ?></div>
                    <div class="label">Absent</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo count($summary) - $stats['present_count'] - $stats['absent_count'] - $stats['marked_count']; ?></div>
                    <div class="label">Not Recorded</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="d-flex justify-content-center gap-3">
                <a href="AttendanceController.php?action=report&start_date=<?php echo $sessionDate; ?>&end_date=<?php echo $sessionDate; ?>" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Generate Report
                </a>
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Summary
                </button>
                <a href="AttendanceController.php?action=daily-summary&date=<?php echo date('Y-m-d', strtotime($sessionDate . ' -1 day')); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left"></i> Previous Day
                </a>
                <a href="AttendanceController.php?action=daily-summary&date=<?php echo date('Y-m-d', strtotime($sessionDate . ' +1 day')); ?>" class="btn btn-outline-primary">
                    Next Day <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="summary-section">
            <!-- HZ-ATT-UI-005: Daily attendance summary display -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-users"></i> Attendance Overview</h4>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">Present: <?php echo $stats['present_count']; ?></span>
                    <span class="badge bg-danger">Absent: <?php echo $stats['absent_count']; ?></span>
                    <span class="badge bg-warning text-dark">Marked: <?php echo $stats['marked_count']; ?></span>
                    <span class="badge bg-secondary">Not Recorded: <?php echo count($summary) - $stats['present_count'] - $stats['absent_count'] - $stats['marked_count']; ?></span>
                </div>
            </div>

            <div class="row">
                <?php if (!empty($summary)): ?>
                    <?php foreach ($summary as $beneficiary): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="beneficiary-row">
                                <div class="beneficiary-info">
                                    <div class="beneficiary-avatar">
                                        <?php echo strtoupper(substr($beneficiary['FirstName'], 0, 1) . substr($beneficiary['LastName'], 0, 1)); ?>
                                    </div>
                                    <div class="beneficiary-details">
                                        <h6><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></h6>
                                        <small>Age: <?php echo htmlspecialchars($beneficiary['Age'] ?? 'N/A'); ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-indicator status-<?php echo $beneficiary['attendance_status']; ?>">
                                        <?php
                                        switch ($beneficiary['attendance_status']) {
                                            case 'present':
                                                echo 'Present';
                                                break;
                                            case 'absent':
                                                echo 'Absent';
                                                break;
                                            case 'marked':
                                                echo 'Marked';
                                                break;
                                            default:
                                                echo 'Not Recorded';
                                        }
                                        ?>
                                    </span>
                                    <?php if ($beneficiary['attendance_status'] !== 'not_recorded'): ?>
                                        <a href="AttendanceController.php?action=view&id=<?php echo $beneficiary['AttendanceID']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="AttendanceController.php?action=create" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> No active beneficiaries found for this date.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
