<?php $pageTitle = 'Volunteers'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .volunteer-page { padding: 30px; }
        .tabs {
            border-bottom: 1px solid #dfe3e8;
            display: flex;
            gap: 32px;
            margin-bottom: 30px;
        }
        .tab-link {
            border-bottom: 2px solid transparent;
            color: #334155;
            font-size: 20px;
            font-weight: 700;
            padding: 16px 20px 12px;
            text-decoration: none;
        }
        .tab-link.active {
            border-color: #1b3a5c;
            color: #071326;
        }
        .section-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 28px;
        }
        .section-head h2 {
            color: #071326;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .assign-btn {
            background: #1b3a5c;
            border: 0;
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            min-height: 50px;
            padding: 10px 22px;
            text-decoration: none;
            white-space: nowrap;
        }
        .assign-btn:hover { color: #fff; background: #2e4a6c; }
        .panel {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.10);
            margin-bottom: 24px;
            padding: 20px;
        }
        .metric {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            border: 1px solid #dfe3e8;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .metric .label { color: #64748b; font-size: 14px; font-weight: 600; }
        .metric .value { color: #071326; font-size: 28px; font-weight: 800; }
        .schedule-table th {
            color: #071326;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-badge {
            border-radius: 999px;
            display: inline-block;
            font-weight: 700;
            padding: 6px 12px;
        }
        .status-scheduled { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-no-show { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>
    <?php $canManageSchedules = in_array(strtolower(rbacCurrentRole()), ['admin', 'staff'], true); ?>

    <main class="container-fluid volunteer-page">
        <nav class="tabs" aria-label="Volunteer tabs">
            <a class="tab-link active" href="VolunteerScheduleController.php?action=list">Schedule View</a>
            <a class="tab-link" href="VolunteerController.php?action=list">Volunteers List</a>
        </nav>

        <div class="section-head">
            <div>
                <h2>Volunteer Schedules</h2>
                <p class="text-muted mb-0">Review assignments, filter by date, and manage shifts.</p>
            </div>
            <?php if ($canManageSchedules): ?>
                <a class="assign-btn" href="VolunteerScheduleController.php?action=create">
                    <i class="fas fa-plus me-2" aria-hidden="true"></i>Assign Volunteer
                </a>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Total Schedules</div>
                    <div class="value"><?php echo (int)($stats['total_schedules'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Scheduled</div>
                    <div class="value"><?php echo (int)($stats['scheduled'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Completed</div>
                    <div class="value"><?php echo (int)($stats['completed'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric">
                    <div class="label">Total Hours</div>
                    <div class="value"><?php echo number_format((float)($stats['total_hours'] ?? 0), 1); ?></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="list">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        <option value="scheduled" <?php echo (($_GET['status'] ?? '') === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="completed" <?php echo (($_GET['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo (($_GET['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="no-show" <?php echo (($_GET['status'] ?? '') === 'no-show') ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a class="btn btn-outline-secondary" href="VolunteerScheduleController.php?action=list">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle schedule-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Volunteer</th>
                            <th>Role</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($schedules)): ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($schedule['ScheduleDate'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr((string)($schedule['StartTime'] ?? ''), 0, 5) . ' - ' . substr((string)($schedule['EndTime'] ?? ''), 0, 5)); ?></td>
                                    <td><strong><?php echo htmlspecialchars($schedule['FullName'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($schedule['Role'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Location'] ?? ''); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars(strtolower($schedule['Status'])); ?>">
                                            <?php echo htmlspecialchars(ucfirst($schedule['Status'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-primary" href="VolunteerScheduleController.php?action=view&id=<?php echo (int)$schedule['ScheduleID']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($canManageSchedules): ?>
                                                <a class="btn btn-outline-warning" href="VolunteerScheduleController.php?action=edit&id=<?php echo (int)$schedule['ScheduleID']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a class="btn btn-outline-danger" href="VolunteerScheduleController.php?action=delete&id=<?php echo (int)$schedule['ScheduleID']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No schedules found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
            <nav aria-label="Schedule pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page ?? 1) <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="VolunteerScheduleController.php?action=list&page=<?php echo max(1, ($page ?? 1) - 1); ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>">Previous</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Page <?php echo (int)($page ?? 1); ?> of <?php echo (int)($totalPages ?? 1); ?></span></li>
                    <li class="page-item <?php echo ($page ?? 1) >= ($totalPages ?? 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="VolunteerScheduleController.php?action=list&page=<?php echo min(($totalPages ?? 1), ($page ?? 1) + 1); ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&from_date=<?php echo urlencode($_GET['from_date'] ?? ''); ?>&to_date=<?php echo urlencode($_GET['to_date'] ?? ''); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
