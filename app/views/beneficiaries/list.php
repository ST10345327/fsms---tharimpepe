<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Management - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
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
        .beneficiary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .beneficiary-card h5 { color: #333; margin-bottom: 10px; }
        .beneficiary-info { font-size: 14px; color: #666; margin: 5px 0; }
        .badge-active { background-color: #28a745; }
        .badge-inactive { background-color: #6c757d; }
        .badge-suspended { background-color: #dc3545; }
        .btn-group-sm { margin-top: 15px; }
        .nav-tabs .nav-link { color: #666; border-color: #e0e0e0; }
        .nav-tabs .nav-link.active { color: #667eea; border-color: #667eea; }
        .search-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
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
                    <h1><i class="fas fa-users"></i> Beneficiary Management</h1>
                    <p class="mb-0 mt-2">Manage meal recipients and registration records</p>
                </div>
                <a href="BeneficiaryController.php?action=create" class="btn btn-light">
                    <i class="fas fa-plus"></i> Register Beneficiary
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
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $totalCount; ?></div>
                    <div class="label">Total Beneficiaries</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['active']; ?></div>
                    <div class="label">Active</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['inactive']; ?></div>
                    <div class="label">Inactive</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="number"><?php echo $statusCounts['suspended']; ?></div>
                    <div class="label">Suspended</div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="row">
                <div class="col-md-6">
                    <form method="GET" action="BeneficiaryController.php" class="d-flex">
                        <input type="hidden" name="action" value="search">
                        <input type="text" name="q" class="form-control me-2" placeholder="Search by name or notes..." required minlength="2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <form method="GET" action="BeneficiaryController.php" class="d-flex flex-grow-1">
                            <input type="hidden" name="action" value="by-date-range">
                            <input type="date" name="start_date" class="form-control me-1" required>
                            <input type="date" name="end_date" class="form-control me-1" required>
                            <button type="submit" class="btn btn-outline-info">
                                <i class="fas fa-calendar"></i> Date Range
                            </button>
                        </form>
                        <form method="GET" action="BeneficiaryController.php" class="d-flex">
                            <input type="hidden" name="action" value="by-age-range">
                            <input type="number" name="min_age" class="form-control me-1" placeholder="Min" min="0" max="120">
                            <input type="number" name="max_age" class="form-control me-1" placeholder="Max" min="0" max="120">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-child"></i> Age Range
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-4">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo !isset($_GET['status']) ? 'active' : ''; ?>"
                       href="BeneficiaryController.php?action=list">
                        All Beneficiaries
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'active' ? 'active' : ''; ?>"
                       href="BeneficiaryController.php?action=list&status=active">
                        Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'active' : ''; ?>"
                       href="BeneficiaryController.php?action=list&status=inactive">
                        Inactive
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['status'] ?? '') === 'suspended' ? 'active' : ''; ?>"
                       href="BeneficiaryController.php?action=list&status=suspended">
                        Suspended
                    </a>
                </li>
            </ul>
        </div>

        <!-- Beneficiary List -->
        <div class="row">
            <?php if (!empty($beneficiaries)): ?>
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="beneficiary-card">
                            <!-- HZ-BEN-UI-001: Beneficiary card display -->
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></h5>
                                    <span class="badge badge-<?php echo $beneficiary['Status']; ?>">
                                        <?php echo ucfirst($beneficiary['Status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="beneficiary-info">
                                <i class="fas fa-birthday-cake"></i>
                                Age: <?php echo htmlspecialchars($beneficiary['Age'] ?? 'Not specified'); ?>
                            </div>
                            <div class="beneficiary-info">
                                <i class="fas fa-calendar-alt"></i>
                                Registered: <?php echo date('d M Y', strtotime($beneficiary['RegistrationDate'])); ?>
                            </div>
                            <?php if (!empty($beneficiary['Notes'])): ?>
                                <div class="beneficiary-info">
                                    <i class="fas fa-sticky-note"></i>
                                    <?php echo htmlspecialchars(substr($beneficiary['Notes'], 0, 50)); ?>
                                    <?php if (strlen($beneficiary['Notes']) > 50): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="btn-group-sm d-flex gap-2">
                                <a href="BeneficiaryController.php?action=view&id=<?php echo $beneficiary['BeneficiaryID']; ?>"
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="BeneficiaryController.php?action=edit&id=<?php echo $beneficiary['BeneficiaryID']; ?>"
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
                        <i class="fas fa-info-circle"></i> No beneficiaries found. <a href="BeneficiaryController.php?action=create">Register a beneficiary</a>
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
