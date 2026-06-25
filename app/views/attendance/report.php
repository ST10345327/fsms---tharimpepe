<?php
$startDate = $selectedStartDate ?? ($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')));
$endDate = $selectedEndDate ?? ($_GET['end_date'] ?? date('Y-m-d'));
$status = $selectedStatus ?? ($_GET['status'] ?? '');
$beneficiaryId = $selectedBeneficiaryId ?? (isset($_GET['beneficiary_id']) ? (int)$_GET['beneficiary_id'] : '');
$search = $selectedSearch ?? ($_GET['search'] ?? '');
$reportData = $reportData ?? [];
$beneficiarySummary = $beneficiarySummary ?? [];
$stats = $stats ?? [
    'total_sessions' => 0,
    'present_count' => 0,
    'absent_count' => 0,
    'marked_count' => 0,
    'unique_beneficiaries' => 0,
];
$presentRate = count($reportData) > 0 ? round(($stats['present_count'] / count($reportData)) * 100, 1) : 0;
?>
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
        .card-shell {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .metric {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .metric .number {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-marked { background-color: #fff3cd; color: #856404; }
        .export-link {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Attendance Reports</h1>
                    <p class="mb-0 mt-2">Filter, review, and export attendance data</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <a class="btn btn-success export-link"
                       href="AttendanceController.php?action=export&mode=report&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?><?php echo $beneficiaryId ? '&beneficiary_id=' . (int)$beneficiaryId : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card-shell p-4 mb-4">
            <form method="GET" action="AttendanceController.php">
                <input type="hidden" name="action" value="report">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="beneficiary_id" class="form-label">Beneficiary</label>
                        <select class="form-select" id="beneficiary_id" name="beneficiary_id">
                            <option value="">All Beneficiaries</option>
                            <?php foreach ($beneficiaries as $beneficiary): ?>
                                <option value="<?php echo (int)$beneficiary['BeneficiaryID']; ?>" <?php echo ((string)$beneficiaryId === (string)$beneficiary['BeneficiaryID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="present" <?php echo $status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="absent" <?php echo $status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="marked" <?php echo $status === 'marked' ? 'selected' : ''; ?>>Marked</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search beneficiary, notes, or attendance ID">
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

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="metric">
                    <div class="number"><?php echo (int)$stats['total_sessions']; ?></div>
                    <div>Total Sessions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="number"><?php echo (int)$stats['present_count']; ?></div>
                    <div>Present</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="number"><?php echo (int)$stats['absent_count']; ?></div>
                    <div>Absent</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="number"><?php echo (int)$stats['unique_beneficiaries']; ?></div>
                    <div>Unique Beneficiaries</div>
                </div>
            </div>
        </div>

        <div class="card-shell p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-table"></i> Detailed Attendance Report</h5>
                <span class="badge bg-primary"><?php echo count($reportData); ?> Records</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="reportTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Beneficiary</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData)): ?>
                            <?php foreach ($reportData as $record): ?>
                                <?php $recordStatus = $record['Status'] ?? 'marked'; ?>
                                <tr>
                                    <td><?php echo !empty($record['SessionDate']) ? date('M d, Y', strtotime($record['SessionDate'])) : 'N/A'; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars(trim(($record['FirstName'] ?? '') . ' ' . ($record['LastName'] ?? ''))); ?></strong>
                                        <br><small class="text-muted">ID: <?php echo htmlspecialchars($record['BeneficiaryID'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($recordStatus); ?>">
                                            <?php echo ucfirst($recordStatus); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['Notes'] ?? ''); ?></td>
                                    <td>
                                        <?php if (!empty($record['SessionType'])): ?>
                                            <?php echo htmlspecialchars($record['SessionType']); ?>
                                            <?php echo !empty($record['Location']) ? ' - ' . htmlspecialchars($record['Location']) : ''; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No attendance records found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-shell p-4">
            <h5 class="mb-3"><i class="fas fa-users"></i> Beneficiary Attendance Summary</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                        <?php if (!empty($beneficiarySummary)): ?>
                            <?php foreach ($beneficiarySummary as $summary): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($summary['FirstName'] . ' ' . $summary['LastName']); ?></strong></td>
                                    <td><?php echo (int)$summary['total_sessions']; ?></td>
                                    <td class="text-success"><?php echo (int)$summary['present_count']; ?></td>
                                    <td class="text-danger"><?php echo (int)$summary['absent_count']; ?></td>
                                    <td class="text-warning"><?php echo (int)$summary['marked_count']; ?></td>
                                    <td><?php echo number_format((float)$summary['attendance_rate'], 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No summary data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
