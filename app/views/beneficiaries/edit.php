<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Beneficiary - FSMS</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .form-label { font-weight: 600; color: #333; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .required { color: #dc3545; }
        .form-section { margin-bottom: 30px; }
        .form-section h4 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .status-suspended { background-color: #fff3cd; color: #856404; }
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
                    <h1><i class="fas fa-user-edit"></i> Edit Beneficiary</h1>
                    <p class="mb-0 mt-2">Update beneficiary information</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="BeneficiaryController.php?action=view&id=<?php echo $beneficiary['BeneficiaryID']; ?>" class="btn btn-light">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="BeneficiaryController.php?action=list" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
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

        <!-- Current Status -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Current Status:</strong>
                            <span class="status-badge status-<?php echo $beneficiary['Status']; ?> ms-2">
                                <?php echo ucfirst($beneficiary['Status']); ?>
                            </span>
                            <br>
                            <small class="text-muted">Last updated: <?php echo date('d M Y H:i', strtotime($beneficiary['UpdatedAt'])); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <!-- HZ-BEN-UI-003: Beneficiary edit form -->
                    <form method="POST" action="BeneficiaryController.php?action=edit&id=<?php echo $beneficiary['BeneficiaryID']; ?>" id="beneficiaryForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                        <!-- Personal Information -->
                        <div class="form-section">
                            <h4><i class="fas fa-user"></i> Personal Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="firstName" name="firstName"
                                           value="<?php echo htmlspecialchars($beneficiary['FirstName']); ?>"
                                           required maxlength="50" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="lastName" name="lastName"
                                           value="<?php echo htmlspecialchars($beneficiary['LastName']); ?>"
                                           required maxlength="50" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age"
                                           value="<?php echo htmlspecialchars($beneficiary['Age'] ?? ''); ?>"
                                           min="0" max="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo $beneficiary['Gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $beneficiary['Gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $beneficiary['Gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <h4><i class="fas fa-address-book"></i> Contact Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?php echo htmlspecialchars($beneficiary['Phone'] ?? ''); ?>"
                                           maxlength="15" pattern="[0-9+\-\s]+" title="Only numbers, spaces, hyphens, and plus signs allowed">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($beneficiary['Email'] ?? ''); ?>"
                                           maxlength="100">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"
                                          maxlength="255"><?php echo htmlspecialchars($beneficiary['Address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Registration Details -->
                        <div class="form-section">
                            <h4><i class="fas fa-clipboard-list"></i> Registration Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="registrationDate" class="form-label">Registration Date <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="registrationDate" name="registrationDate"
                                           value="<?php echo htmlspecialchars($beneficiary['RegistrationDate']); ?>"
                                           required max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?php echo $beneficiary['Status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $beneficiary['Status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo $beneficiary['Status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4"
                                          maxlength="500" placeholder="Any additional information about the beneficiary..."><?php echo htmlspecialchars($beneficiary['Notes'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="BeneficiaryController.php?action=list" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Beneficiary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this beneficiary?</p>
                    <p class="text-danger"><strong><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></strong></p>
                    <p class="text-muted">This action cannot be undone. All beneficiary data will be permanently removed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="BeneficiaryController.php?action=delete&id=<?php echo $beneficiary['BeneficiaryID']; ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>"
                       class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Beneficiary
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('beneficiaryForm').addEventListener('submit', function(e) {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const registrationDate = document.getElementById('registrationDate').value;

            if (firstName.length < 2) {
                alert('First name must be at least 2 characters long.');
                e.preventDefault();
                return;
            }

            if (lastName.length < 2) {
                alert('Last name must be at least 2 characters long.');
                e.preventDefault();
                return;
            }

            const today = new Date().toISOString().split('T')[0];
            if (registrationDate > today) {
                alert('Registration date cannot be in the future.');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
