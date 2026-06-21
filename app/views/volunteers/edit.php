<?php
$pageTitle = 'Edit Volunteer';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit"></i> Edit Volunteer Profile</h1>
        </div>
    </div>

    <!-- Form -->
    <div class="container pt-4 pb-5">
        <!-- HZ-VOL-UI-003: Volunteer edit form -->
        <div class="form-container">
            <!-- Messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($volunteer): ?>
                <form method="POST" action="VolunteerController.php?action=edit">
                    <input type="hidden" name="volunteer_id" value="<?php echo htmlspecialchars($volunteer['VolunteerID']); ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($volunteer['FirstName']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($volunteer['LastName']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($volunteer['Email']); ?>" disabled>
                        <small class="text-muted">Cannot be changed here</small>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($volunteer['Phone']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($volunteer['Address'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Availability Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" <?php echo $volunteer['AvailabilityStatus'] === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="unavailable" <?php echo $volunteer['AvailabilityStatus'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                            <option value="on_leave" <?php echo $volunteer['AvailabilityStatus'] === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="VolunteerController.php?action=view&id=<?php echo $volunteer['VolunteerID']; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
