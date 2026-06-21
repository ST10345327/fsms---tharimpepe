<?php
$pageTitle = 'Attendance Details';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-clipboard-check"></i> Attendance Details</h1>
                    <p class="mb-0 mt-2">View complete attendance record information</p>
                </div>
                <a href="AttendanceController.php?action=list" class="btn btn-light">
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
                <a href="AttendanceController.php?action=edit&id=<?php echo $attendance['AttendanceID']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Record
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete Record
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Details
                </button>
            </div>
        </div>

        <!-- Attendance Details -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="details-card">
                    <!-- HZ-ATT-UI-004: Attendance details display -->
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($attendance['FirstName'], 0, 1) . substr($attendance['LastName'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($attendance['FirstName'] . ' ' . $attendance['LastName']); ?></h3>
                        <span class="status-badge status-<?php echo $attendance['Status']; ?>">
                            <?php echo ucfirst($attendance['Status']); ?>
                        </span>
                        <p class="mt-2 text-muted">
                            Session Date: <?php echo date('l, F d, Y', strtotime($attendance['SessionDate'])); ?>
                        </p>
                    </div>

                    <!-- Attendance Information -->
                    <div class="info-section">
                        <h5><i class="fas fa-clipboard-check"></i> Attendance Information</h5>
                        <div class="info-item">
                            <span class="info-label">Attendance ID:</span>
                            <span class="info-value"><?php echo htmlspecialchars($attendance['AttendanceID']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Session Date:</span>
                            <span class="info-value"><?php echo date('F d, Y', strtotime($attendance['SessionDate'])); ?> (<?php echo date('l', strtotime($attendance['SessionDate'])); ?>)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-<?php echo $attendance['Status']; ?>">
                                    <?php echo ucfirst($attendance['Status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Recorded At:</span>
                            <span class="info-value"><?php echo date('F d, Y \a\t H:i:s', strtotime($attendance['CreatedAt'])); ?></span>
                        </div>
                    </div>

                    <!-- Beneficiary Information -->
                    <div class="info-section">
                        <h5><i class="fas fa-user"></i> Beneficiary Information</h5>
                        <div class="info-item">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($attendance['FirstName'] . ' ' . $attendance['LastName']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span class="info-value"><?php echo htmlspecialchars($attendance['Age'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span class="info-value"><?php echo htmlspecialchars($attendance['Gender'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Registration Date:</span>
                            <span class="info-value"><?php echo date('F d, Y', strtotime($attendance['RegistrationDate'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Beneficiary Status:</span>
                            <span class="info-value">
                                <span class="badge bg-<?php echo $attendance['BeneficiaryStatus'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($attendance['BeneficiaryStatus']); ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <?php if (!empty($attendance['Notes'])): ?>
                        <div class="info-section">
                            <h5><i class="fas fa-sticky-note"></i> Additional Notes</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($attendance['Notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Beneficiary Attendance History -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="beneficiary-history">
                    <h5 class="mb-3"><i class="fas fa-history"></i> Recent Attendance History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get beneficiary attendance history (placeholder - would be fetched from model)
                                $history = []; // In real implementation, get from AttendanceModel::getBeneficiaryAttendance()
                                if (empty($history)):
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> This is the only attendance record for this beneficiary.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($history as $record): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['SessionDate'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $record['Status'] === 'present' ? 'success' : ($record['Status'] === 'absent' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($record['Status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($record['Notes'] ?? '', 0, 30)); ?><?php echo strlen($record['Notes'] ?? '') > 30 ? '...' : ''; ?></td>
                                        <td><?php echo date('M d, H:i', strtotime($record['CreatedAt'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
                    <p>Are you sure you want to delete this attendance record?</p>
                    <p class="text-danger">
                        <strong><?php echo htmlspecialchars($attendance['FirstName'] . ' ' . $attendance['LastName']); ?></strong>
                        - <?php echo date('F d, Y', strtotime($attendance['SessionDate'])); ?>
                        (<?php echo ucfirst($attendance['Status']); ?>)
                    </p>
                    <p class="text-muted">This action cannot be undone. The attendance record will be permanently removed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="AttendanceController.php?action=delete&id=<?php echo $attendance['AttendanceID']; ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>"
                       class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Record
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
