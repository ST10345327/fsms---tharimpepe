<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Details - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .detail-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
        }
        .detail-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            font-size: 1.3rem;
            color: #333;
            font-weight: 600;
            margin-top: 10px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #218838 0%, #1aa085 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-gift"></i> Donation Details</h1>
                    <p class="mb-0 mt-2">Detailed donation information</p>
                </div>
                <a href="DonationController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="content-card">
                    <h3 class="mb-4"><?php echo htmlspecialchars($donation['DonorName']); ?></h3>

                    <!-- Donor Information Section -->
                    <div class="detail-section">
                        <h5 class="mb-3">Donor Information</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Donor Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($donation['DonorName']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">
                                    <?php if (!empty($donation['DonorEmail'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($donation['DonorEmail']); ?>">
                                            <?php echo htmlspecialchars($donation['DonorEmail']); ?>
                                        </a>
                                    <?php else: ?>
                                        <em class="text-muted">Not provided</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Donation Details Section -->
                    <div class="detail-section">
                        <h5 class="mb-3">Donation Details</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value">
                                    <span class="badge" style="background: <?php 
                                        echo match($donation['DonationType']) {
                                            'cash' => '#28a745',
                                            'food' => '#fd7e14',
                                            'supplies' => '#17a2b8',
                                            default => '#6c757d'
                                        };
                                    ?>; color: white; padding: 8px 16px;">
                                        <?php echo ucfirst($donation['DonationType']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value">
                                    <?php if ((float)$donation['Amount'] > 0): ?>
                                        R<?php echo number_format((float)$donation['Amount'], 2); ?>
                                    <?php else: ?>
                                        <em class="text-muted">N/A</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Donation Date</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($donation['DonationDate'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($donation['Description'])): ?>
                        <div class="detail-section">
                            <h5 class="mb-3">Description</h5>
                            <p><?php echo htmlspecialchars($donation['Description']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Recorded Information -->
                    <div class="detail-section">
                        <h5 class="mb-3">System Information</h5>
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Donation ID</div>
                                <div class="detail-value">#<?php echo (int)$donation['DonationID']; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Recorded</div>
                                <div class="detail-value"><?php echo date('M d, Y \a\t h:i A', strtotime($donation['CreatedAt'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons mt-4">
                        <a href="DonationController.php?action=edit&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Donation
                        </a>
                        <a href="DonationController.php?action=delete&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="content-card text-center">
                    <i class="fas fa-gift fa-5x mb-3" style="color: #28a745;"></i>
                    <h5><?php echo ucfirst($donation['DonationType']); ?> Donation</h5>
                    <?php if ((float)$donation['Amount'] > 0): ?>
                        <p class="display-5 text-success">R<?php echo number_format((float)$donation['Amount'], 2); ?></p>
                    <?php endif; ?>
                    <p class="text-muted">From <?php echo htmlspecialchars($donation['DonorName']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
