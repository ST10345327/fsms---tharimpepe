<?php
$pageTitle = 'Delete Donation';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete Donation</h1>
            <p class="mb-0 mt-2">Warning: This action cannot be undone</p>
        </div>
    </div>

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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
