<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Schedule - FSMS</title>
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
        .confirmation-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .icon-danger {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .detail-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #667eea;
        }
        .btn-danger { background: #dc3545; border: none; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete Schedule</h1>
            <p class="mb-0 mt-2">Confirm deletion of schedule</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="confirmation-card">
                    <div class="icon-danger">
                        <i class="fas fa-trash"></i>
                    </div>

                    <h2 class="mb-3">Delete Schedule?</h2>
                    <p class="text-muted mb-4">
                        Are you sure you want to delete this schedule? This action cannot be undone.
                    </p>

                    <!-- Schedule Information -->
                    <div class="detail-info">
                        <div class="info-row">
                            <span class="info-label">Volunteer:</span>
                            <span><?php echo htmlspecialchars($schedule['FullName']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date:</span>
                            <span><?php echo date('F d, Y', strtotime($schedule['ScheduleDate'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Time:</span>
                            <span><?php echo substr($schedule['StartTime'], 0, 5) . ' - ' . substr($schedule['EndTime'], 0, 5); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Location:</span>
                            <span><?php echo htmlspecialchars($schedule['Location']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="badge bg-warning"><?php echo htmlspecialchars($schedule['Status']); ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <form method="POST" action="VolunteerScheduleController.php" class="d-grid gap-2">
                        <input type="hidden" name="action" value="destroy">
                        <input type="hidden" name="schedule_id" value="<?php echo (int)$schedule['ScheduleID']; ?>">

                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-trash"></i> Yes, Delete Schedule
                        </button>
                        
                        <a href="VolunteerScheduleController.php?action=view&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                           class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
