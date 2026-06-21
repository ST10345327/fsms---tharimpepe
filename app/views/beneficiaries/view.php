<?php
$pageTitle = 'Beneficiary Details';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user"></i> Beneficiary Details</h1>
                    <p class="mb-0 mt-2">View complete beneficiary information</p>
                </div>
                <a href="BeneficiaryController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

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

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="d-flex justify-content-center gap-3">
                <a href="BeneficiaryController.php?action=edit&id=<?php echo $beneficiary['BeneficiaryID']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Beneficiary
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete Beneficiary
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Details
                </button>
            </div>
        </div>

        <!-- Beneficiary Details -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="details-card">
                    <!-- HZ-BEN-UI-004: Beneficiary details display -->
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($beneficiary['FirstName'], 0, 1) . substr($beneficiary['LastName'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></h3>
                        <span class="status-badge status-<?php echo $beneficiary['Status']; ?>">
                            <?php echo ucfirst($beneficiary['Status']); ?>
                        </span>
                    </div>

                    <!-- Personal Information -->
                    <div class="info-section">
                        <h5><i class="fas fa-user"></i> Personal Information</h5>
                        <div class="info-item">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['Age'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['Gender'] ?? 'Not specified'); ?></span>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="info-section">
                        <h5><i class="fas fa-address-book"></i> Contact Information</h5>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['Phone'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['Email'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['Address'] ?? 'Not provided'); ?></span>
                        </div>
                    </div>

                    <!-- Registration Information -->
                    <div class="info-section">
                        <h5><i class="fas fa-clipboard-list"></i> Registration Information</h5>
                        <div class="info-item">
                            <span class="info-label">Beneficiary ID:</span>
                            <span class="info-value"><?php echo htmlspecialchars($beneficiary['BeneficiaryID']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Registration Date:</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($beneficiary['RegistrationDate'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-<?php echo $beneficiary['Status']; ?>">
                                    <?php echo ucfirst($beneficiary['Status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Updated:</span>
                            <span class="info-value"><?php echo date('d M Y H:i', strtotime($beneficiary['UpdatedAt'])); ?></span>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <?php if (!empty($beneficiary['Notes'])): ?>
                        <div class="info-section">
                            <h5><i class="fas fa-sticky-note"></i> Additional Notes</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($beneficiary['Notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
