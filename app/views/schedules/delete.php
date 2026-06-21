<?php
$pageTitle = 'Delete Schedule';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete Schedule</h1>
            <p class="mb-0 mt-2">Confirm deletion of schedule</p>
        </div>
    </div>

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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
