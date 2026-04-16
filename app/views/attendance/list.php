<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - FSMS</title>
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
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-card .number { font-size: 32px; font-weight: 700; color: #667eea; }
        .stats-card .label { color: #666; font-size: 14px; }
        .attendance-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .attendance-card h5 { color: #333; margin-bottom: 10px; }
        .attendance-info { font-size: 14px; color: #666; margin: 5px 0; }
        .badge-present { background-color: #28a745; }
        .badge-absent { background-color: #dc3545; }
        .badge-marked { background-color: #ffc107; color: #000; }
        .btn-group-sm { margin-top: 15px; }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .nav-tabs .nav-link { color: #666; border-color: #e0e0e0; }
        .nav-tabs .nav-link.active { color: #667eea; border-color: #667eea; }
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
                    <h1><i class="fas fa-clipboard-check"></i> Attendance Management</h1>
                    <p class="mb-0 mt-2">Track meal distribution and beneficiary attendance</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="AttendanceController.php?action=create" class="btn btn-light">
                        <i class="fas fa-plus"></i> Record Attendance
                    </a>
                    <a href="AttendanceController.php?action=daily-summary" class="btn btn-light">
                        <i class="fas fa-calendar-day"></i> Daily Summary
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Messages -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <?php if ($stats): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $stats['total_sessions']; ?></div>
                    <div class="label">Total Sessions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $stats['present_count']; ?></div>
                    <div class="label">Present</div>
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
                    <div class="number"><?php echo $stats['unique_beneficiaries']; ?></div>
                    <div class="label">Unique Beneficiaries</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <form method="GET" action="AttendanceController.php" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <input type="date" name="date" class="form-control me-2"
                               value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-filter"></i> Filter by Date
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <a href="AttendanceController.php?action=bulk-record" class="btn btn-success">
                            <i class="fas fa-users"></i> Bulk Record
                        </a>
                        <a href="AttendanceController.php?action=report" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" action="AttendanceController.php" class="d-flex">
                        <input type="hidden" name="action" value="list">
                        <select name="status" class="form-select me-2">
                            <option value="">All Status</option>
                            <option value="present" <?php echo ($statusFilter === 'present') ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo ($statusFilter === 'absent') ? 'selected' : ''; ?>>Absent</option>
                            <option value="marked" <?php echo ($statusFilter === 'marked') ? 'selected' : ''; ?>>Marked</option>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="row">
            <?php if (!empty($attendance)): ?>
                <?php foreach ($attendance as $record): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="attendance-card">
                            <!-- HZ-ATT-UI-001: Attendance record display -->
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5><?php echo htmlspecialchars($record['FirstName'] . ' ' . $record['LastName']); ?></h5>
                                    <span class="badge badge-<?php echo $record['Status']; ?>">
                                        <?php echo ucfirst($record['Status']); ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($record['SessionDate'])); ?>
                                </small>
                            </div>

                            <div class="attendance-info">
                                <i class="fas fa-birthday-cake"></i>
                                Age: <?php echo htmlspecialchars($record['Age'] ?? 'N/A'); ?>
                            </div>
                            <div class="attendance-info">
                                <i class="fas fa-clock"></i>
                                Recorded: <?php echo date('M d, H:i', strtotime($record['CreatedAt'])); ?>
                            </div>
                            <?php if (!empty($record['Notes'])): ?>
                                <div class="attendance-info">
                                    <i class="fas fa-sticky-note"></i>
                                    <?php echo htmlspecialchars(substr($record['Notes'], 0, 50)); ?>
                                    <?php if (strlen($record['Notes']) > 50): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="btn-group-sm d-flex gap-2">
                                <a href="AttendanceController.php?action=view&id=<?php echo $record['AttendanceID']; ?>"
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="AttendanceController.php?action=edit&id=<?php echo $record['AttendanceID']; ?>"
                                   class="btn btn-sm btn-outline-warning flex-grow-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No attendance records found.
                        <?php if ($dateFilter || $statusFilter): ?>
                            Try adjusting your filters or <a href="AttendanceController.php?action=list">clear filters</a>.
                        <?php else: ?>
                            <a href="AttendanceController.php?action=create">Record your first attendance</a>.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (count($attendance) >= 20): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Attendance pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=list&page=<?php echo $page - 1; ?><?php echo $dateFilter ? '&date=' . $dateFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item active">
                        <span class="page-link"><?php echo $page; ?></span>
                    </li>

                    <li class="page-item">
                        <a class="page-link" href="?action=list&page=<?php echo $page + 1; ?><?php echo $dateFilter ? '&date=' . $dateFilter : ''; ?><?php echo $statusFilter ? '&status=' . $statusFilter : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
