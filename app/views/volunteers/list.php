<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .navbar-brand { font-weight: 700; color: white !important; }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-card .number { font-size: 32px; font-weight: 700; color: #667eea; }
        .stats-card .label { color: #666; font-size: 14px; }
        .volunteer-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .volunteer-card h5 { color: #333; margin-bottom: 10px; }
        .volunteer-info { font-size: 14px; color: #666; margin: 5px 0; }
        .badge-available { background-color: #28a745; }
        .badge-unavailable { background-color: #dc3545; }
        .badge-on_leave { background-color: #ffc107; color: #333; }
        .btn-group-sm { margin-top: 15px; }
        .nav-tabs .nav-link { color: #666; border-color: #e0e0e0; }
        .nav-tabs .nav-link.active { color: #667eea; border-color: #667eea; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-users"></i> Volunteer Management</h1>
                    <p class="mb-0 mt-2">Manage volunteer profiles and schedules</p>
                </div>
                <a href="VolunteerController.php?action=create" class="btn btn-light">
                    <i class="fas fa-plus"></i> Register Volunteer
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
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

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['available']; ?></div>
                    <div class="label">Available</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['unavailable']; ?></div>
                    <div class="label">Unavailable</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['on_leave']; ?></div>
                    <div class="label">On Leave</div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-4">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" 
                       href="VolunteerController.php?action=list">
                        All Volunteers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'available' ? 'active' : ''; ?>" 
                       href="VolunteerController.php?action=list&status=available">
                        Available
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'unavailable' ? 'active' : ''; ?>" 
                       href="VolunteerController.php?action=list&status=unavailable">
                        Unavailable
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'on_leave' ? 'active' : ''; ?>" 
                       href="VolunteerController.php?action=list&status=on_leave">
                        On Leave
                    </a>
                </li>
            </ul>
        </div>

        <!-- Volunteer List -->
        <div class="row">
            <?php if (!empty($volunteers)): ?>
                <?php foreach ($volunteers as $volunteer): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="volunteer-card">
                            <!-- HZ-VOL-UI-001: Volunteer card display -->
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5><?php echo htmlspecialchars($volunteer['FirstName'] . ' ' . $volunteer['LastName']); ?></h5>
                                    <span class="badge badge-<?php echo str_replace('_', '-', $volunteer['AvailabilityStatus']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $volunteer['AvailabilityStatus'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="volunteer-info">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($volunteer['Email']); ?>
                            </div>
                            <div class="volunteer-info">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($volunteer['Phone']); ?>
                            </div>
                            <div class="volunteer-info">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($volunteer['Address'] ?? 'Not provided'); ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="btn-group-sm d-flex gap-2">
                                <a href="VolunteerController.php?action=view&id=<?php echo $volunteer['VolunteerID']; ?>" 
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="VolunteerController.php?action=edit&id=<?php echo $volunteer['VolunteerID']; ?>" 
                                   class="btn btn-sm btn-outline-warning flex-grow-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No volunteers found. <a href="VolunteerController.php?action=create">Register a volunteer</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
