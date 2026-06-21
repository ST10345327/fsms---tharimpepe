<?php
$pageTitle = 'Schedule Details';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
