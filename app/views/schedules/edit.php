<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule - FSMS</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-section h5 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit"></i> Edit Schedule</h1>
            <p class="mb-0 mt-2">Update schedule information</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <form method="POST" action="VolunteerScheduleController.php" class="needs-validation">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="schedule_id" value="<?php echo (int)$schedule['ScheduleID']; ?>">

                        <!-- Volunteer Information -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-user"></i> Volunteer Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Volunteer</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($schedule['FullName']); ?>" disabled>
                                <small class="text-muted">Volunteer cannot be changed. Delete and create new schedule to change volunteer.</small>
                            </div>
                        </div>

                        <!-- Schedule Details -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-calendar"></i> Schedule Details</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="schedule_date" 
                                       value="<?php echo htmlspecialchars($schedule['ScheduleDate']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="start_time" 
                                           value="<?php echo substr($schedule['StartTime'], 0, 5); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="end_time" 
                                           value="<?php echo substr($schedule['EndTime'], 0, 5); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="location" 
                                       value="<?php echo htmlspecialchars($schedule['Location']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select role...</option>
                                    <option value="Coordinator" <?php echo $schedule['Role'] === 'Coordinator' ? 'selected' : ''; ?>>Coordinator</option>
                                    <option value="Assistant" <?php echo $schedule['Role'] === 'Assistant' ? 'selected' : ''; ?>>Assistant</option>
                                    <option value="Driver" <?php echo $schedule['Role'] === 'Driver' ? 'selected' : ''; ?>>Driver</option>
                                    <option value="Cook" <?php echo $schedule['Role'] === 'Cook' ? 'selected' : ''; ?>>Cook</option>
                                    <option value="Cleaner" <?php echo $schedule['Role'] === 'Cleaner' ? 'selected' : ''; ?>>Cleaner</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status & Hours -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-check"></i> Status</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status_select" required>
                                    <option value="scheduled" <?php echo $schedule['Status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="completed" <?php echo $schedule['Status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $schedule['Status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="no-show" <?php echo $schedule['Status'] === 'no-show' ? 'selected' : ''; ?>>No Show</option>
                                </select>
                            </div>

                            <div class="mb-3" id="hours_worked_field" style="display: <?php echo $schedule['Status'] === 'completed' ? 'block' : 'none'; ?>">
                                <label class="form-label">Hours Worked</label>
                                <input type="number" class="form-control" name="hours_worked" step="0.5" min="0"
                                       value="<?php echo htmlspecialchars($schedule['HoursWorked'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-sticky-note"></i> Notes</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($schedule['Notes'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="VolunteerScheduleController.php?action=view&id=<?php echo (int)$schedule['ScheduleID']; ?>" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('status_select').addEventListener('change', function() {
            const hoursField = document.getElementById('hours_worked_field');
            hoursField.style.display = this.value === 'completed' ? 'block' : 'none';
        });
    </script>
</body>
</html>
