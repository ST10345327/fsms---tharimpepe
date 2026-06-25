<?php
$pageTitle = 'Make a Donation';
require_once __DIR__ . '/../includes/layout-header.php';
$donorName = $currentUser['username'] ?? '';
$donorEmail = $currentUser['email'] ?? '';
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="fsms-card">
            <div class="fsms-card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="DonorController.php?action=create_donation" id="donorDonationForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                    <div style="margin-bottom:24px;">
                        <h5 style="font-weight:700;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--fsms-green);">
                            <i class="fas fa-user"></i> Your Information
                        </h5>
                        <div class="mb-3">
                            <label for="DonorName" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="DonorName" name="DonorName" required maxlength="150"
                                   value="<?php echo htmlspecialchars($_POST['DonorName'] ?? $donorName); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="DonorEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="DonorEmail" name="DonorEmail" maxlength="100"
                                   value="<?php echo htmlspecialchars($_POST['DonorEmail'] ?? $donorEmail); ?>">
                        </div>
                    </div>

                    <div style="margin-bottom:24px;">
                        <h5 style="font-weight:700;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--fsms-green);">
                            <i class="fas fa-gift"></i> Donation Details
                        </h5>
                        <div class="mb-3">
                            <label for="DonationType" class="form-label">Donation Type</label>
                            <select class="form-select" id="DonationType" name="DonationType" required onchange="toggleAmountField()">
                                <option value="">-- Select --</option>
                                <option value="cash"<?php echo ($_POST['DonationType'] ?? '') === 'cash' ? ' selected' : ''; ?>>Cash</option>
                                <option value="food"<?php echo ($_POST['DonationType'] ?? '') === 'food' ? ' selected' : ''; ?>>Food Items</option>
                                <option value="supplies"<?php echo ($_POST['DonationType'] ?? '') === 'supplies' ? ' selected' : ''; ?>>Supplies</option>
                                <option value="other"<?php echo ($_POST['DonationType'] ?? '') === 'other' ? ' selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="amountGroup">
                            <label for="Amount" class="form-label">Amount (Rands)</label>
                            <input type="number" class="form-control" id="Amount" name="Amount" placeholder="0.00" min="0" step="0.01"
                                   value="<?php echo htmlspecialchars($_POST['Amount'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="DonationDate" class="form-label">Donation Date</label>
                            <input type="date" class="form-control" id="DonationDate" name="DonationDate" required max="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($_POST['DonationDate'] ?? date('Y-m-d')); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="Description" class="form-label">Description (optional)</label>
                            <textarea class="form-control" id="Description" name="Description" rows="3" maxlength="500"
                                      placeholder="e.g., Items donated, any notes"><?php echo htmlspecialchars($_POST['Description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-center mt-4">
                        <a href="DonorController.php?action=dashboard" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-check-circle"></i> Record Donation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAmountField() {
    var type = document.getElementById('DonationType').value;
    var group = document.getElementById('amountGroup');
    var input = document.getElementById('Amount');
    if (type === 'cash') {
        group.style.display = 'block';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
toggleAmountField();
document.getElementById('donorDonationForm').addEventListener('submit', function(e) {
    var type = document.getElementById('DonationType').value;
    var amount = parseFloat(document.getElementById('Amount').value || 0);
    if (type === 'cash' && amount <= 0) {
        alert('Please enter a valid amount for cash donations.');
        e.preventDefault();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
