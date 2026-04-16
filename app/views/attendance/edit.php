<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance - FSMS</title>
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
        .current-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-marked { background-color: #fff3cd; color: #856404; }
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
                    <h1><i class="fas fa-edit"></i> Edit Attendance</h1>
                    <p class="mb-0 mt-2">Update attendance record details</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="AttendanceController.php?action=view&id=<?php echo $attendance['AttendanceID']; ?>" class="btn btn-light">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="AttendanceController.php?action=list" class="btn btn-light">
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

        <!-- Current Attendance Info -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="current-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($attendance['FirstName'] . ' ' . $attendance['LastName']); ?>
                            </h6>
                            <p class="mb-1 text-muted">
                                Session Date: <?php echo date('l, F d, Y', strtotime($attendance['SessionDate'])); ?>
                            </p>
                            <small class="text-muted">
                                Recorded: <?php echo date('F d, Y \a\t H:i', strtotime($attendance['CreatedAt'])); ?>
                            </small>
                        </div>
                        <span class="status-badge status-<?php echo $attendance['Status']; ?>">
                            <?php echo ucfirst($attendance['Status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <!-- HZ-ATT-UI-003: Attendance edit form -->
                    <form method="POST" action="AttendanceController.php?action=edit&id=<?php echo $attendance['AttendanceID']; ?>" id="attendanceForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="attendance_id" value="<?php echo $attendance['AttendanceID']; ?>">

                        <!-- Beneficiary Selection -->
                        <div class="mb-4">
                            <label for="beneficiarySelect" class="form-label">Beneficiary <span class="required">*</span></label>
                            <select class="form-select" id="beneficiarySelect" name="beneficiary_id" required>
                                <option value="">Select beneficiary...</option>
                                <?php foreach ($beneficiaries as $beneficiary): ?>
                                    <option value="<?php echo $beneficiary['BeneficiaryID']; ?>"
                                            <?php echo $beneficiary['BeneficiaryID'] == $attendance['BeneficiaryID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?>
                                        (Age: <?php echo htmlspecialchars($beneficiary['Age'] ?? 'N/A'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Change beneficiary if this record was entered for the wrong person</div>
                        </div>

                        <!-- Session Date -->
                        <div class="mb-4">
                            <label for="sessionDate" class="form-label">Session Date <span class="required">*</span></label>
                            <input type="date" class="form-control" id="sessionDate" name="session_date"
                                   value="<?php echo htmlspecialchars($attendance['SessionDate']); ?>"
                                   required max="<?php echo date('Y-m-d'); ?>">
                            <div class="form-text">Date when the meal was distributed</div>
                        </div>

                        <!-- Attendance Status -->
                        <div class="mb-4">
                            <label for="status" class="form-label">Attendance Status <span class="required">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="present" <?php echo $attendance['Status'] === 'present' ? 'selected' : ''; ?>>Present - Beneficiary received meal</option>
                                <option value="absent" <?php echo $attendance['Status'] === 'absent' ? 'selected' : ''; ?>>Absent - Beneficiary did not receive meal</option>
                                <option value="marked" <?php echo $attendance['Status'] === 'marked' ? 'selected' : ''; ?>>Marked - Special circumstances</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Any additional notes about this attendance record..."><?php echo htmlspecialchars($attendance['Notes'] ?? ''); ?></textarea>
                            <div class="form-text">Optional notes about the attendance or meal distribution</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="AttendanceController.php?action=list" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Attendance
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

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            const sessionDate = document.getElementById('sessionDate').value;
            const beneficiaryId = document.getElementById('beneficiarySelect').value;

            if (!sessionDate) {
                alert('Please select a session date.');
                e.preventDefault();
                return;
            }

            if (!beneficiaryId) {
                alert('Please select a beneficiary.');
                e.preventDefault();
                return;
            }

            const today = new Date().toISOString().split('T')[0];
            if (sessionDate > today) {
                alert('Session date cannot be in the future.');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
