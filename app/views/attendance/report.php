<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - FSMS</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .stat-card .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-card .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .table-responsive { border-radius: 10px; overflow: hidden; }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        .table tbody tr:hover { background-color: #f8f9fa; }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-marked { background-color: #fff3cd; color: #856404; }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
        }
        .export-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
            color: white;
        }
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
                    <h1><i class="fas fa-chart-bar"></i> Attendance Reports</h1>
                    <p class="mb-0 mt-2">Comprehensive attendance analytics and reporting</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button type="button" class="btn btn-success export-btn" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Report Filters -->
        <div class="form-card">
            <h4 class="mb-4"><i class="fas fa-filter"></i> Report Filters</h4>
            <form method="GET" action="AttendanceController.php" class="filter-section">
                <input type="hidden" name="action" value="report">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="beneficiary_id" class="form-label">Beneficiary</label>
                        <select class="form-select" id="beneficiary_id" name="beneficiary_id">
                            <option value="">All Beneficiaries</option>
                            <?php foreach ($beneficiaries as $beneficiary): ?>
                                <option value="<?php echo $beneficiary['BeneficiaryID']; ?>"
                                    <?php echo ($beneficiaryId == $beneficiary['BeneficiaryID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="present" <?php echo ($status == 'present') ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo ($status == 'absent') ? 'selected' : ''; ?>>Absent</option>
                            <option value="marked" <?php echo ($status == 'marked') ? 'selected' : ''; ?>>Marked</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                    <a href="AttendanceController.php?action=report" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <?php if (!empty($reportData)): ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_sessions']; ?></div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_attendance']; ?></div>
                        <div class="stat-label">Total Attendance</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($stats['attendance_rate'], 1); ?>%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['unique_beneficiaries']; ?></div>
                        <div class="stat-label">Active Beneficiaries</div>
                    </div>
                </div>
            </div>

            <!-- Attendance Chart -->
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-chart-pie"></i> Attendance Distribution</h5>
                <canvas id="attendanceChart" width="400" height="200"></canvas>
            </div>

            <!-- Detailed Report Table -->
            <div class="report-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Detailed Attendance Report</h5>
                    <span class="badge bg-primary"><?php echo count($reportData); ?> Records</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="reportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Beneficiary</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['SessionDate'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($record['FirstName'] . ' ' . $record['LastName']); ?></strong>
                                        <br><small class="text-muted">ID: <?php echo $record['BeneficiaryID']; ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $record['Status']; ?>">
                                            <i class="fas fa-<?php echo $record['Status'] == 'present' ? 'check' : ($record['Status'] == 'absent' ? 'times' : 'exclamation-triangle'); ?>"></i>
                                            <?php echo ucfirst($record['Status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['Notes'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($record['RecordedBy'] ?? 'System'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Beneficiary Summary -->
            <div class="report-card">
                <h5 class="mb-3"><i class="fas fa-users"></i> Beneficiary Attendance Summary</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Beneficiary</th>
                                <th>Total Sessions</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Marked</th>
                                <th>Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($beneficiarySummary as $summary): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($summary['FirstName'] . ' ' . $summary['LastName']); ?></strong>
                                    </td>
                                    <td><?php echo $summary['total_sessions']; ?></td>
                                    <td><span class="text-success"><?php echo $summary['present_count']; ?></span></td>
                                    <td><span class="text-danger"><?php echo $summary['absent_count']; ?></span></td>
                                    <td><span class="text-warning"><?php echo $summary['marked_count']; ?></span></td>
                                    <td>
                                        <div class="progress" style="width: 100px;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: <?php echo $summary['attendance_rate']; ?>%"
                                                 aria-valuenow="<?php echo $summary['attendance_rate']; ?>"
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo number_format($summary['attendance_rate'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="report-card">
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Report Data Available</h5>
                    <p class="text-muted">Adjust your filters or check if attendance data exists for the selected period.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Attendance Chart
        <?php if (!empty($stats)): ?>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Marked'],
                datasets: [{
                    data: [<?php echo $stats['present_count']; ?>, <?php echo $stats['absent_count']; ?>, <?php echo $stats['marked_count']; ?>],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        <?php endif; ?>

        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('reportTable');
            if (!table) {
                alert('No data to export');
                return;
            }

            let csv = [];
            const rows = table.querySelectorAll('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');

                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].textContent.trim();
                    // Remove icons and clean up status badges
                    text = text.replace(/[\u{1F600}-\u{1F64F}]/gu, ''); // Remove emojis
                    text = text.replace(/fa-\w+/g, ''); // Remove font awesome classes
                    row.push('"' + text.replace(/"/g, '""') + '"');
                }

                csv.push(row.join(','));
            }

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');

            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'attendance_report_<?php echo date('Y-m-d'); ?>.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        // Print functionality
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
