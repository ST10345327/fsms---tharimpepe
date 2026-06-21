<?php
$pageTitle = 'Create Schedule';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-calendar-plus"></i> Create Schedule</h1>
            <p class="mb-0 mt-2">Assign a volunteer to a shift</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <form method="POST" action="VolunteerScheduleController.php" class="needs-validation">
                        <input type="hidden" name="action" value="store">

                        <!-- Volunteer Selection -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-user"></i> Volunteer</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Select Volunteer <span class="text-danger">*</span></label>
                                <select class="form-select" name="volunteer_id" required>
                                    <option value="">Choose a volunteer...</option>
                                    <?php foreach ($volunteers as $volunteer): ?>
                                        <?php
                                        $displayName = trim($volunteer['FullName'] ?? '');
                                        if ($displayName === '') {
                                            $displayName = trim(($volunteer['FirstName'] ?? '') . ' ' . ($volunteer['LastName'] ?? ''));
                                        }
                                        if ($displayName === '') {
                                            $displayName = 'Volunteer #' . (int)$volunteer['VolunteerID'];
                                        }
                                        ?>
                                        <option value="<?php echo (int)$volunteer['VolunteerID']; ?>">
                                            <?php echo htmlspecialchars($displayName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Schedule Details -->
                        <div class="form-section mb-4">
                            <h5><i class="fas fa-calendar"></i> Schedule Details</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="schedule_date" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="location" required 
                                       placeholder="e.g., Community Center, School">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select role...</option>
                                    <option value="Coordinator">Coordinator</option>
                                    <option value="Assistant">Assistant</option>
                                    <option value="Driver">Driver</option>
                                    <option value="Cook">Cook</option>
                                    <option value="Cleaner">Cleaner</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3" 
                                          placeholder="Additional notes about the schedule..."></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Create Schedule
                            </button>
                            <a href="VolunteerScheduleController.php?action=list" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
