<?php
$pageTitle = 'Record Donation';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-plus-circle"></i> Record New Donation</h1>
            <p class="mb-0 mt-2">Add a donation from a donor to the system</p>
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

        <!-- Form Card -->
        <div class="form-card">
            <form method="POST" action="DonationController.php?action=create" id="donationForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <!-- HZ-DON-UI-001: Donor Information Section -->
                <div class="form-section">
                    <h5><i class="fas fa-user"></i> Donor Information</h5>

                    <div class="form-group">
                        <label for="DonorName" class="form-label required">Donor Name</label>
                        <input type="text" class="form-control" id="DonorName" name="DonorName" 
                               placeholder="Full name of the donor" required maxlength="150"
                               value="<?php echo htmlspecialchars($_POST['DonorName'] ?? ''); ?>">
                        <div class="helper-text">Enter the full name of the individual or organization making the donation</div>
                    </div>

                    <div class="form-group">
                        <label for="DonorEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="DonorEmail" name="DonorEmail" 
                               placeholder="donor@example.com" maxlength="100"
                               value="<?php echo htmlspecialchars($_POST['DonorEmail'] ?? ''); ?>">
                        <div class="helper-text">Contact email for acknowledgment and communications (optional)</div>
                    </div>
                </div>

                <!-- HZ-DON-UI-002: Donation Details Section -->
                <div class="form-section">
                    <h5><i class="fas fa-gift"></i> Donation Details</h5>

                    <div class="form-group">
                        <label for="DonationType" class="form-label required">Donation Type</label>
                        <select class="form-select" id="DonationType" name="DonationType" required onchange="updateAmountField()">
                            <option value="">-- Select Donation Type --</option>
                            <option value="cash" <?php echo ($_POST['DonationType'] ?? '') === 'cash' ? 'selected' : ''; ?>>Cash Donation</option>
                            <option value="food" <?php echo ($_POST['DonationType'] ?? '') === 'food' ? 'selected' : ''; ?>>Food Items</option>
                            <option value="supplies" <?php echo ($_POST['DonationType'] ?? '') === 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                            <option value="other" <?php echo ($_POST['DonationType'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <div class="helper-text">Select the type of donation being made</div>
                    </div>

                    <div class="form-group" id="amountGroup">
                        <label for="Amount" class="form-label">Amount (Rands)</label>
                        <input type="number" class="form-control" id="Amount" name="Amount" 
                               placeholder="0.00" min="0" step="0.01"
                               value="<?php echo htmlspecialchars($_POST['Amount'] ?? ''); ?>">
                        <div class="helper-text">Enter the cash amount in South African Rands</div>
                    </div>

                    <div class="form-group">
                        <label for="DonationDate" class="form-label required">Donation Date</label>
                        <input type="date" class="form-control" id="DonationDate" name="DonationDate" 
                               required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($_POST['DonationDate'] ?? date('Y-m-d')); ?>">
                        <div class="helper-text">Date the donation was received</div>
                    </div>
                </div>

                <!-- HZ-DON-UI-003: Additional Information Section -->
                <div class="form-section">
                    <h5><i class="fas fa-sticky-note"></i> Additional Information</h5>

                    <div class="form-group">
                        <label for="Description" class="form-label">Description</label>
                        <textarea class="form-control" id="Description" name="Description" rows="3" 
                                  placeholder="e.g., Items donated, special instructions, donor notes" 
                                  maxlength="500"><?php echo htmlspecialchars($_POST['Description'] ?? ''); ?></textarea>
                        <div class="helper-text">Add any additional details about this donation (items list, special requests, etc.)</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="button-group mt-4">
                    <a href="DonationController.php?action=list" class="btn btn-secondary" style="padding: 12px 30px; font-size: 1rem;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Record Donation
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
                amountInput.value = '';
            }
        }

        // Initialize on page load
        updateAmountField();

        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const donationType = document.getElementById('DonationType').value;
            const amount = parseFloat(document.getElementById('Amount').value || 0);

            if (donationType === 'cash' && amount <= 0) {
                alert('Please enter a valid amount for cash donations.');
                e.preventDefault();
            }
        });
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
