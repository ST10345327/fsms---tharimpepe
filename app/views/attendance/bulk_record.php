<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Attendance Recording - FSMS</title>
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
        .beneficiary-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        .beneficiary-item:hover { border-color: #667eea; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .beneficiary-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .beneficiary-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .beneficiary-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .beneficiary-details h6 { margin: 0; color: #333; }
        .beneficiary-details small { color: #666; }
        .status-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .status-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        .status-btn:hover { border-color: #667eea; }
        .status-btn.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        .status-present.active { border-color: #28a745; background: #28a745; }
        .status-absent.active { border-color: #dc3545; background: #dc3545; }
        .status-marked.active { border-color: #ffc107; background: #ffc107; color: #000; }
        .notes-input { margin-top: 10px; }
        .notes-input input { width: 100%; border-radius: 4px; border: 1px solid #ddd; padding: 5px 10px; }
        .bulk-actions {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); }
        .progress-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
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
                    <h1><i class="fas fa-users"></i> Bulk Attendance Recording</h1>
                    <p class="mb-0 mt-2">Record attendance for multiple beneficiaries at once</p>
                </div>
                <a href="AttendanceController.php?action=daily-summary&date=<?php echo $sessionDate; ?>" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to Summary
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

        <!-- Session Date -->
        <div class="bulk-actions">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5><i class="fas fa-calendar-alt"></i> Session Date: <?php echo date('l, F d, Y', strtotime($sessionDate)); ?></h5>
                    <form method="GET" action="AttendanceController.php" class="d-flex mt-2">
                        <input type="hidden" name="action" value="bulk-record">
                        <input type="date" name="date" class="form-control me-2"
                               value="<?php echo htmlspecialchars($sessionDate); ?>" max="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-sync"></i> Change Date
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-success" onclick="markAllPresent()">
                            <i class="fas fa-check-circle"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearAll()">
                            <i class="fas fa-undo"></i> Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Recording Form -->
        <form method="POST" action="AttendanceController.php?action=bulk-record" id="bulkAttendanceForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="session_date" value="<?php echo htmlspecialchars($sessionDate); ?>">

            <!-- HZ-ATT-UI-006: Bulk attendance recording interface -->
            <div class="form-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Beneficiary Attendance</h4>
                    <span class="badge bg-primary"><?php echo count($beneficiaries); ?> Beneficiaries</span>
                </div>

                <div class="row">
                    <?php if (!empty($beneficiaries)): ?>
                        <?php foreach ($beneficiaries as $index => $beneficiary): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="beneficiary-item">
                                    <div class="beneficiary-header">
                                        <div class="beneficiary-info">
                                            <div class="beneficiary-avatar">
                                                <?php echo strtoupper(substr($beneficiary['FirstName'], 0, 1) . substr($beneficiary['LastName'], 0, 1)); ?>
                                            </div>
                                            <div class="beneficiary-details">
                                                <h6><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></h6>
                                                <small>Age: <?php echo htmlspecialchars($beneficiary['Age'] ?? 'N/A'); ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="attendance[<?php echo $index; ?>][beneficiary_id]" value="<?php echo $beneficiary['BeneficiaryID']; ?>">

                                    <div class="status-selector">
                                        <button type="button" class="status-btn status-present"
                                                onclick="setStatus(<?php echo $index; ?>, 'present', this)">
                                            <i class="fas fa-check"></i> Present
                                        </button>
                                        <button type="button" class="status-btn status-absent"
                                                onclick="setStatus(<?php echo $index; ?>, 'absent', this)">
                                            <i class="fas fa-times"></i> Absent
                                        </button>
                                        <button type="button" class="status-btn status-marked"
                                                onclick="setStatus(<?php echo $index; ?>, 'marked', this)">
                                            <i class="fas fa-exclamation-triangle"></i> Marked
                                        </button>
                                    </div>

                                    <div class="notes-input">
                                        <input type="text" name="attendance[<?php echo $index; ?>][notes]"
                                               placeholder="Optional notes..." maxlength="255">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle"></i> No active beneficiaries found.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Progress Summary -->
                <div class="progress-summary">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="h5 mb-0" id="total-count"><?php echo count($beneficiaries); ?></div>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h5 mb-0 text-success" id="present-count">0</div>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h5 mb-0 text-danger" id="absent-count">0</div>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h5 mb-0 text-warning" id="marked-count">0</div>
                            <small class="text-muted">Marked</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="form-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-info-circle"></i>
                            Only beneficiaries with selected status will be recorded. Unselected beneficiaries will be skipped.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="AttendanceController.php?action=daily-summary&date=<?php echo $sessionDate; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Attendance
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let attendanceData = {};

        function setStatus(index, status, button) {
            // Remove active class from all buttons in this group
            const buttons = button.parentElement.querySelectorAll('.status-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked button
            button.classList.add('active');

            // Store the selection
            attendanceData[index] = { status: status };

            updateSummary();
        }

        function markAllPresent() {
            const buttons = document.querySelectorAll('.status-present');
            buttons.forEach(button => {
                const index = Array.from(button.parentElement.parentElement.parentElement.children).indexOf(button.parentElement.parentElement);
                setStatus(Array.from(button.closest('.row').children).indexOf(button.closest('.col-md-6, .col-lg-4')), 'present', button);
            });
        }

        function clearAll() {
            // Remove active class from all buttons
            document.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));

            // Clear attendance data
            attendanceData = {};

            updateSummary();
        }

        function updateSummary() {
            const present = Object.values(attendanceData).filter(item => item.status === 'present').length;
            const absent = Object.values(attendanceData).filter(item => item.status === 'absent').length;
            const marked = Object.values(attendanceData).filter(item => item.status === 'marked').length;

            document.getElementById('present-count').textContent = present;
            document.getElementById('absent-count').textContent = absent;
            document.getElementById('marked-count').textContent = marked;
        }

        // Form validation
        document.getElementById('bulkAttendanceForm').addEventListener('submit', function(e) {
            const selectedCount = Object.keys(attendanceData).length;

            if (selectedCount === 0) {
                alert('Please select attendance status for at least one beneficiary.');
                e.preventDefault();
                return;
            }

            // Add hidden inputs for selected attendance
            for (const [index, data] of Object.entries(attendanceData)) {
                // Find the corresponding form inputs and ensure they're included
                const beneficiaryInput = document.querySelector(`input[name="attendance[${index}][beneficiary_id]"]`);
                if (beneficiaryInput) {
                    // Add status to the form data
                    let statusInput = document.querySelector(`input[name="attendance[${index}][status]"]`);
                    if (!statusInput) {
                        statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = `attendance[${index}][status]`;
                        beneficiaryInput.parentElement.appendChild(statusInput);
                    }
                    statusInput.value = data.status;
                }
            }
        });
    </script>
</body>
</html>
