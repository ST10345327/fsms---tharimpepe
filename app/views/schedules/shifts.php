<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Shifts - FSMS</title>
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
        .date-selector {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .shift-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .shift-time {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            min-width: 120px;
        }
        .shift-info {
            flex-grow: 1;
            padding: 0 20px;
        }
        .shift-volunteer {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .shift-meta {
            font-size: 0.9rem;
            color: #666;
        }
        .shift-meta span {
            margin-right: 20px;
        }
        .shift-status {
            display: flex;
            gap: 10px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .status-scheduled { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-clock"></i> Daily Shifts</h1>
            <p class="mb-0 mt-2">View all shifts for a specific date</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Date Selector -->
        <div class="date-selector">
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="action" value="shifts">
                
                <input type="date" class="form-control" name="date" 
                       value="<?php echo htmlspecialchars($selectedDate); ?>">
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search"></i> View Shifts
                </button>
            </form>
        </div>

        <!-- Shifts List -->
        <div class="row">
            <div class="col-lg-10">
                <div class="mb-3">
                    <h5>Shifts for <?php echo date('F d, Y', strtotime($selectedDate)); ?></h5>
                </div>

                <?php if (!empty($shifts)): ?>
                    <?php foreach ($shifts as $shift): ?>
                        <div class="shift-card">
                            <div class="shift-time">
                                <?php echo substr($shift['StartTime'], 0, 5); ?><br>
                                <span style="font-size: 0.8rem; color: #999;">
                                    <?php echo substr($shift['EndTime'], 0, 5); ?>
                                </span>
                            </div>

                            <div class="shift-info">
                                <div class="shift-volunteer"><?php echo htmlspecialchars($shift['FullName']); ?></div>
                                <div class="shift-meta">
                                    <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($shift['Role']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($shift['Location']); ?></span>
                                </div>
                            </div>

                            <div class="shift-status">
                                <span class="status-badge status-<?php echo strtolower($shift['Status']); ?>">
                                    <?php echo ucfirst($shift['Status']); ?>
                                </span>
                                <a href="VolunteerScheduleController.php?action=view&id=<?php echo (int)$shift['ScheduleID']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>No shifts scheduled</h5>
                        <p>No volunteers are scheduled for this date</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Summary Card -->
            <div class="col-lg-2">
                <div class="bg-white rounded-3 p-3 shadow-sm" style="position: sticky; top: 20px;">
                    <h6 class="mb-3"><i class="fas fa-info-circle"></i> Summary</h6>
                    
                    <div class="mb-3">
                        <div class="text-muted small">Total Shifts</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #667eea;">
                            <?php echo count($shifts); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Scheduled</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #28a745;">
                            <?php echo count(array_filter($shifts, fn($s) => $s['Status'] === 'scheduled')); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Completed</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #17a2b8;">
                            <?php echo count(array_filter($shifts, fn($s) => $s['Status'] === 'completed')); ?>
                        </div>
                    </div>

                    <hr>

                    <a href="VolunteerScheduleController.php?action=create" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-plus"></i> Add Shift
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
