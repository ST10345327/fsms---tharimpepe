<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Donation - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .donation-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .donation-info div {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
        }
        .info-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 1.2rem;
            color: #333;
            font-weight: 600;
            margin-top: 8px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .btn-danger:hover { background: linear-gradient(135deg, #c82333 0%, #b01b2e 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete Donation</h1>
            <p class="mb-0 mt-2">Warning: This action cannot be undone</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="content-card">
                    <!-- Warning Section -->
                    <div class="warning-box">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h5 class="mb-2">Are you sure?</h5>
                                <p class="mb-0">Deleting this donation will permanently remove it from the system. This action is irreversible and cannot be undone.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Donation Information -->
                    <h5 class="mb-3">Donation to be deleted:</h5>
                    <div class="donation-info">
                        <div>
                            <div class="info-item">
                                <div class="info-label">Donor Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($donation['DonorName']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value">
                                    <span class="badge" style="background: <?php 
                                        echo match($donation['DonationType']) {
                                            'cash' => '#28a745',
                                            'food' => '#fd7e14',
                                            'supplies' => '#17a2b8',
                                            default => '#6c757d'
                                        };
                                    ?>; color: white; padding: 6px 12px;">
                                        <?php echo ucfirst($donation['DonationType']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Amount</div>
                                <div class="info-value">
                                    <?php if ((float)$donation['Amount'] > 0): ?>
                                        R<?php echo number_format((float)$donation['Amount'], 2); ?>
                                    <?php else: ?>
                                        <em class="text-muted">N/A</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Date</div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($donation['DonationDate'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation Paragraph -->
                    <p class="alert alert-danger mb-4">
                        <strong>IMPORTANT:</strong> If this donation was allocated to food stock, you will need to update the food stock records manually after deletion.
                    </p>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="DonationController.php?action=list" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <form method="POST" action="DonationController.php" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$donation['DonationID']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Yes, Delete Donation
                            </button>
                        </form>
                    </div>

                    <!-- Additional Info -->
                    <hr class="my-4">
                    <div class="alert alert-info" role="alert">
                        <strong><i class="fas fa-info-circle"></i> Tip:</strong> If you want to keep donation records for historical purposes, consider archiving or disabling donations instead of deleting them.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
