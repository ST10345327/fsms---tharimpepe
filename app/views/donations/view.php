<?php
$pageTitle = 'Donation Details';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
