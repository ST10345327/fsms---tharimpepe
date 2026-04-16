<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Schedule Report - FSMS</title>
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
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-calendar-alt"></i> Volunteer Schedule Report</h1>
            <p class="mb-0 mt-2">Volunteer shift assignments and schedule tracking</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Schedule Records</h6>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="volunteer_schedule">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="">All Status</option>
                        <option value="Scheduled" <?php echo ($_GET['status'] ?? '') === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="Completed" <?php echo ($_GET['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="No-Show" <?php echo ($_GET['status'] ?? '') === 'No-Show' ? 'selected' : ''; ?>>No-Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Volunteer</label>
                    <input type="text" class="form-control" name="volunteer" value="<?php echo htmlspecialchars($_GET['volunteer'] ?? ''); ?>" placeholder="Name">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="ReportsController.php?action=volunteer_schedule" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Schedule Table -->
        <div class="table-card">
            <h5 class="mb-3">
                Schedule Records
                <small class="text-muted">(<?php echo count($scheduleData ?? []); ?> records)</small>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Volunteer</th>
                            <th>Shift Date</th>
                            <th>Shift Time</th>
                            <th>Duration (hrs)</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($scheduleData)): ?>
                            <?php foreach ($scheduleData as $entry): ?>
                                <?php
                                    $status = $entry['Status'] ?? 'Scheduled';
                                    $statusBg = match($status) {
                                        'Completed' => 'success',
                                        'Cancelled' => 'danger',
                                        'No-Show' => 'warning',
                                        default => 'info'
                                    };
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($entry['VolunteerName'] ?? ''); ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['ScheduledDate'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($entry['StartTime'] ?? '-'); ?> - <?php echo htmlspecialchars($entry['EndTime'] ?? '-'); ?></td>
                                    <td><?php echo number_format((float)($entry['Duration'] ?? 0), 1); ?></td>
                                    <td><?php echo htmlspecialchars($entry['Location'] ?? '-'); ?></td>
                                    <td><span class="badge bg-<?php echo $statusBg; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($entry['Notes'] ?? '-'); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No schedule records found</td>
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
