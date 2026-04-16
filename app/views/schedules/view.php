<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Details - FSMS</title>
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
        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #667eea;
        }
        .detail-value {
            color: #333;
        }
        .status-scheduled { background: #d1ecf1; color: #0c5460; padding: 5px 10px; border-radius: 5px; }
        .status-completed { background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 5px; }
        .status-cancelled { background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 5px; }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-calendar-check"></i> Schedule Details</h1>
                    <p class="mb-0 mt-2">View schedule information</p>
                </div>
                <div>
                    <a href="VolunteerScheduleController.php?action=edit&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                       class="btn btn-light me-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="VolunteerScheduleController.php?action=list" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Schedule Information -->
                <div class="detail-card">
                    <h4 class="mb-4"><i class="fas fa-calendar"></i> Schedule Information</h4>
                    
                    <div class="detail-row">
                        <div class="detail-label">Schedule ID:</div>
                        <div class="detail-value">HZ-SCHED-<?php echo str_pad($schedule['ScheduleID'], 3, '0', STR_PAD_LEFT); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Date:</div>
                        <div class="detail-value"><?php echo date('F d, Y', strtotime($schedule['ScheduleDate'])); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Time:</div>
                        <div class="detail-value">
                            <?php echo substr($schedule['StartTime'], 0, 5); ?> - 
                            <?php echo substr($schedule['EndTime'], 0, 5); ?>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Duration:</div>
                        <div class="detail-value">
                            <?php
                                $start = new DateTime($schedule['ScheduleDate'] . ' ' . $schedule['StartTime']);
                                $end = new DateTime($schedule['ScheduleDate'] . ' ' . $schedule['EndTime']);
                                $interval = $start->diff($end);
                                echo $interval->h . ' hours ' . $interval->i . ' minutes';
                            ?>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">
                            <span class="status-<?php echo strtolower($schedule['Status']); ?>">
                                <?php echo ucfirst($schedule['Status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Volunteer Information -->
                <div class="detail-card">
                    <h4 class="mb-4"><i class="fas fa-user"></i> Volunteer Information</h4>
                    
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($schedule['FullName']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">
                            <a href="mailto:<?php echo htmlspecialchars($schedule['Email']); ?>">
                                <?php echo htmlspecialchars($schedule['Email']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($schedule['Phone']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Role:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($schedule['Role']); ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Location:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($schedule['Location']); ?></div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="detail-card">
                    <h4 class="mb-4"><i class="fas fa-info-circle"></i> Additional Details</h4>
                    
                    <?php if (!empty($schedule['HoursWorked']) && $schedule['Status'] === 'completed'): ?>
                        <div class="detail-row">
                            <div class="detail-label">Hours Worked:</div>
                            <div class="detail-value"><?php echo (float)$schedule['HoursWorked']; ?> hours</div>
                        </div>
                    <?php endif; ?>

                    <div class="detail-row">
                        <div class="detail-label">Notes:</div>
                        <div class="detail-value">
                            <?php echo !empty($schedule['Notes']) ? htmlspecialchars($schedule['Notes']) : '<span class="text-muted">No notes</span>'; ?>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Created:</div>
                        <div class="detail-value"><?php echo date('F d, Y H:i', strtotime($schedule['CreatedAt'] ?? 'now')); ?></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <?php if ($schedule['Status'] !== 'completed'): ?>
                        <a href="VolunteerScheduleController.php?action=edit&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-edit"></i> Edit Schedule
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($schedule['Status'] === 'scheduled'): ?>
                        <a href="VolunteerScheduleController.php?action=delete&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                           class="btn btn-danger btn-lg">
                            <i class="fas fa-trash"></i> Delete Schedule
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
