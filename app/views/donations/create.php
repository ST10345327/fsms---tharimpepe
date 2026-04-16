<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Donation - FSMS</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #28a745;
        }
        .form-group { margin-bottom: 20px; }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #28a745;
            box-shadow: none;
        }
        .required::after {
            content: ' *';
            color: #dc3545;
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .helper-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        .type-info {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-plus-circle"></i> Record New Donation</h1>
            <p class="mb-0 mt-2">Add a donation from a donor to the system</p>
        </div>
    </div>

    <!-- Main Content -->
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
</body>
</html>
