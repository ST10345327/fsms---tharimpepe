<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Results - FSMS</title>
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
                    <h1><i class="fas fa-calendar-alt"></i> Date Range Results</h1>
                    <p class="mb-0 mt-2">
                        <?php echo date('d M Y', strtotime($startDate)); ?> - <?php echo date('d M Y', strtotime($endDate)); ?>
                    </p>
                </div>
                <a href="BeneficiaryController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
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

        <!-- Results -->
        <div class="row">
            <?php if (!empty($beneficiaries)): ?>
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="beneficiary-card">
                            <h5><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></h5>
                            <span class="badge badge-<?php echo $beneficiary['Status']; ?>">
                                <?php echo ucfirst($beneficiary['Status']); ?>
                            </span>
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
                            <div class="btn-group-sm d-flex gap-2 mt-3">
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
                        <i class="fas fa-info-circle"></i> No beneficiaries found in this date range.
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