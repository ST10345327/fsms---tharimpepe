<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Attendance - FSMS</title>
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
        .required { color: #dc3545; }
        .beneficiary-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .beneficiary-card:hover { border-color: #667eea; background-color: #f8f9ff; }
        .beneficiary-card.selected { border-color: #667eea; background-color: #f0f2ff; }
        .beneficiary-name { font-weight: 600; color: #333; }
        .beneficiary-details { color: #666; font-size: 14px; }
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
                    <h1><i class="fas fa-clipboard-check"></i> Record Attendance</h1>
                    <p class="mb-0 mt-2">Mark beneficiary attendance for meal distribution</p>
                </div>
                <a href="AttendanceController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
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

        <!-- Quick Actions -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="AttendanceController.php?action=daily-summary" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-day"></i> View Today's Summary
                    </a>
                    <a href="AttendanceController.php?action=bulk-record" class="btn btn-outline-success">
                        <i class="fas fa-users"></i> Bulk Record Attendance
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <!-- HZ-ATT-UI-002: Attendance recording form -->
                    <form method="POST" action="AttendanceController.php?action=create" id="attendanceForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                        <!-- Session Date -->
                        <div class="mb-4">
                            <label for="sessionDate" class="form-label">Session Date <span class="required">*</span></label>
                            <input type="date" class="form-control" id="sessionDate" name="session_date"
                                   value="<?php echo htmlspecialchars($_POST['session_date'] ?? date('Y-m-d')); ?>"
                                   required max="<?php echo date('Y-m-d'); ?>">
                            <div class="form-text">Select the date when the meal was distributed</div>
                        </div>

                        <!-- Beneficiary Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select Beneficiary <span class="required">*</span></label>
                            <select class="form-select" id="beneficiarySelect" name="beneficiary_id" required>
                                <option value="">Choose a beneficiary...</option>
                                <?php foreach ($beneficiaries as $beneficiary): ?>
                                    <option value="<?php echo $beneficiary['BeneficiaryID']; ?>"
                                            <?php echo (isset($_POST['beneficiary_id']) && $_POST['beneficiary_id'] == $beneficiary['BeneficiaryID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?>
                                        (Age: <?php echo htmlspecialchars($beneficiary['Age'] ?? 'N/A'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Attendance Status -->
                        <div class="mb-4">
                            <label for="status" class="form-label">Attendance Status <span class="required">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="present" <?php echo ($_POST['status'] ?? 'present') === 'present' ? 'selected' : ''; ?>>Present - Beneficiary received meal</option>
                                <option value="absent" <?php echo ($_POST['status'] ?? '') === 'absent' ? 'selected' : ''; ?>>Absent - Beneficiary did not receive meal</option>
                                <option value="marked" <?php echo ($_POST['status'] ?? '') === 'marked' ? 'selected' : ''; ?>>Marked - Special circumstances</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Any additional notes about this attendance record..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            <div class="form-text">Optional notes about the attendance or meal distribution</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="AttendanceController.php?action=list" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Record Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <h5 class="mb-3"><i class="fas fa-history"></i> Recent Attendance Records</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Beneficiary</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get recent attendance (last 5 records)
                                $recentAttendance = array_slice($beneficiaries, 0, 5); // Placeholder - in real app, get from model
                                foreach ($recentAttendance as $beneficiary):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></td>
                                    <td><?php echo date('M d, Y'); ?></td>
                                    <td><span class="badge bg-secondary">Not Recorded</span></td>
                                    <td><?php echo date('H:i'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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

        // Auto-focus on beneficiary selection
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('beneficiarySelect').focus();
        });
    </script>
</body>
</html>
