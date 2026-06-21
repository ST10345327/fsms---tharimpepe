<?php
$pageTitle = 'Volunteer Profile';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-user"></i> Volunteer Profile</h1>
        </div>
    </div>

    <!-- Profile -->
    <div class="container pt-4 pb-5">
        <!-- HZ-VOL-UI-004: Volunteer profile view -->
        <div class="profile-container">
            <?php if ($volunteer): ?>
                <div class="profile-header">
                    <h2>
                        <?php echo htmlspecialchars($volunteer['FirstName'] . ' ' . $volunteer['LastName']); ?>
                        <span class="badge badge-<?php echo str_replace('_', '-', $volunteer['AvailabilityStatus']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $volunteer['AvailabilityStatus'])); ?>
                        </span>
                    </h2>
                </div>

                <div class="info-section">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($volunteer['Username']); ?></div>
                </div>

                <div class="info-section">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($volunteer['Email']); ?></div>
                </div>

                <div class="info-section">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($volunteer['Phone']); ?></div>
                </div>

                <div class="info-section">
                    <div class="info-label">Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($volunteer['Address'] ?? 'Not provided'); ?></div>
                </div>

                <div class="info-section">
                    <div class="info-label">Registered Since</div>
                    <div class="info-value"><?php echo date('d M Y', strtotime($volunteer['CreatedAt'])); ?></div>
                </div>

                <div class="action-buttons d-flex gap-2">
                    <a href="VolunteerController.php?action=edit&id=<?php echo $volunteer['VolunteerID']; ?>" class="btn btn-warning flex-grow-1">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="VolunteerController.php?action=delete&id=<?php echo $volunteer['VolunteerID']; ?>" 
                       class="btn btn-danger flex-grow-1" 
                       onclick="return confirm('Are you sure you want to deactivate this volunteer?');">
                        <i class="fas fa-trash"></i> Deactivate
                    </a>
                    <a href="VolunteerController.php?action=list" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> Volunteer not found.
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
