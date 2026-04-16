<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Profile - FSMS</title>
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
        .profile-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-header { border-bottom: 2px solid #ecf0f1; padding-bottom: 20px; margin-bottom: 20px; }
        .profile-header h2 { color: #333; margin: 0; }
        .badge { margin-left: 10px; }
        .info-section { margin: 20px 0; }
        .info-label { font-weight: 600; color: #667eea; font-size: 12px; text-transform: uppercase; }
        .info-value { color: #333; font-size: 16px; margin-top: 5px; }
        .action-buttons { margin-top: 30px; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
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

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
