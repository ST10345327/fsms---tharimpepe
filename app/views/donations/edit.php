<?php
$pageTitle = 'Edit Donation';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit"></i> Edit Donation</h1>
            <p class="mb-0 mt-2">Update donation information</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <!-- HZ-DON-UI-004: Current Donation Info -->
            <div class="status-info">
                <strong>Donation ID:</strong> #<?php echo (int)$donation['DonationID']; ?> |
                <strong>Recorded:</strong> <?php echo date('M d, Y', strtotime($donation['CreatedAt'])); ?>
            </div>

            <form method="POST" action="DonationController.php?action=edit&id=<?php echo (int)$donation['DonationID']; ?>" id="donationForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- HZ-DON-UI-005: Donor Information -->
                <div class="form-section">
                    <h5><i class="fas fa-user"></i> Donor Information</h5>

                    <div class="form-group">
                        <label for="DonorName" class="form-label required">Donor Name</label>
                        <input type="text" class="form-control" id="DonorName" name="DonorName" 
                               placeholder="Full name of the donor" required maxlength="150"
                               value="<?php echo htmlspecialchars($donation['DonorName']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="DonorEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="DonorEmail" name="DonorEmail" 
                               placeholder="donor@example.com" maxlength="100"
                               value="<?php echo htmlspecialchars($donation['DonorEmail'] ?? ''); ?>">
                    </div>
                </div>

                <!-- HZ-DON-UI-006: Donation Details -->
                <div class="form-section">
                    <h5><i class="fas fa-gift"></i> Donation Details</h5>

                    <div class="form-group">
                        <label for="DonationType" class="form-label required">Donation Type</label>
                        <select class="form-select" id="DonationType" name="DonationType" required onchange="updateAmountField()">
                            <option value="cash" <?php echo $donation['DonationType'] === 'cash' ? 'selected' : ''; ?>>Cash Donation</option>
                            <option value="food" <?php echo $donation['DonationType'] === 'food' ? 'selected' : ''; ?>>Food Items</option>
                            <option value="supplies" <?php echo $donation['DonationType'] === 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                            <option value="other" <?php echo $donation['DonationType'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group" id="amountGroup">
                        <label for="Amount" class="form-label">Amount (Rands)</label>
                        <input type="number" class="form-control" id="Amount" name="Amount" 
                               placeholder="0.00" min="0" step="0.01"
                               value="<?php echo htmlspecialchars($donation['Amount'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="DonationDate" class="form-label required">Donation Date</label>
                        <input type="date" class="form-control" id="DonationDate" name="DonationDate" 
                               required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($donation['DonationDate']); ?>">
                    </div>
                </div>

                <!-- HZ-DON-UI-007: Additional Information -->
                <div class="form-section">
                    <h5><i class="fas fa-sticky-note"></i> Additional Information</h5>

                    <div class="form-group">
                        <label for="Description" class="form-label">Description</label>
                        <textarea class="form-control" id="Description" name="Description" rows="3" 
                                  maxlength="500"><?php echo htmlspecialchars($donation['Description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="button-group mt-4">
                    <a href="DonationController.php?action=view&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-secondary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Donation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateAmountField() {
            const donationType = document.getElementById('DonationType').value;
            const amountGroup = document.getElementById('amountGroup');
            const amountInput = document.getElementById('Amount');

            if (donationType === 'cash') {
                amountGroup.style.display = 'block';
                amountInput.required = true;
            } else {
                amountGroup.style.display = 'none';
                amountInput.required = false;
            }
        }
        updateAmountField();
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
