<?php
$pageTitle = 'Register Volunteer';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-user-plus"></i> Register New Volunteer</h1>
            <p class="mb-0 mt-2">Fill in the form below to register a new volunteer</p>
        </div>
    </div>

    <!-- Form -->
    <div class="container pt-4 pb-5">
        <!-- HZ-VOL-UI-002: Volunteer registration form -->
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

            <form method="POST" action="VolunteerController.php?action=create">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <small class="text-muted">This will be used for login</small>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+27 123 456 7890" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Street address, city, zip code"></textarea>
                </div>

                <div class="d-flex gap-2 pt-3">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-save"></i> Register Volunteer
                    </button>
                    <a href="VolunteerController.php?action=list" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>

            <div class="alert alert-info mt-4" role="alert">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> A temporary password will be generated and displayed after registration. The volunteer should change it on first login.
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
