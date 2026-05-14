<?php
$pageTitle = 'Attendance';
$totalBeneficiaries = count($beneficiaries);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .attendance-page {
            padding: 30px;
        }

        .control-card,
        .summary-card,
        .attendance-table-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
        }

        .control-card {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 30px 32px;
        }

        .date-control {
            align-items: center;
            display: flex;
            gap: 18px;
        }

        .date-control label {
            align-items: center;
            color: #1f2a44;
            display: flex;
            font-size: 18px;
            font-weight: 700;
            gap: 12px;
            margin: 0;
        }

        .date-control .form-control {
            border-radius: 10px;
            color: #1b3a5c;
            font-size: 20px;
            min-height: 52px;
            width: 214px;
        }

        .bulk-buttons {
            display: flex;
            gap: 16px;
        }

        .bulk-buttons .btn {
            font-size: 16px;
            min-height: 46px;
            padding-left: 22px;
            padding-right: 22px;
        }

        .summary-grid {
            display: grid;
            gap: 30px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 30px;
        }

        .summary-card {
            padding: 30px 32px;
        }

        .summary-label {
            color: #1f2a44;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .summary-value {
            color: #071326;
            font-size: 36px;
            font-weight: 400;
            line-height: 1;
        }

        .summary-value.present { color: #00b341; }
        .summary-value.absent { color: #e60012; }

        .attendance-table-card {
            overflow: hidden;
        }

        .prototype-table {
            margin: 0;
        }

        .prototype-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
            padding: 18px 30px;
        }

        .prototype-table tbody td {
            border-color: #e5e7eb;
            color: #071326;
            font-size: 18px;
            padding: 26px 30px;
            vertical-align: middle;
        }

        .present-toggle {
            border-radius: 10px;
            font-size: 20px;
            font-weight: 700;
            min-width: 98px;
            padding: 12px 20px;
        }

        .present-toggle.yes {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #009a35;
        }

        .present-toggle.no {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #e60012;
        }

        .notes-input {
            border-radius: 6px;
            min-height: 38px;
        }

        .save-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
            padding: 22px;
        }

        @media (max-width: 900px) {
            .control-card,
            .date-control {
                align-items: stretch;
                flex-direction: column;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid attendance-page">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <section class="control-card">
            <form method="GET" action="AttendanceController.php" class="date-control">
                <input type="hidden" name="action" value="bulk-record">
                <label for="attendanceDate">
                    <i class="far fa-calendar" aria-hidden="true"></i>
                    Attendance Date:
                </label>
                <input type="date" id="attendanceDate" name="date" class="form-control"
                       value="<?php echo htmlspecialchars($sessionDate); ?>" max="<?php echo date('Y-m-d'); ?>">
            </form>
            <div class="bulk-buttons">
                <button type="button" class="btn btn-success" onclick="markAll('present')">Mark All Present</button>
                <button type="button" class="btn btn-secondary" onclick="markAll('absent')">Mark All Absent</button>
            </div>
        </section>

        <section class="summary-grid" aria-label="Attendance counters">
            <div class="summary-card">
                <div class="summary-label">Present</div>
                <div class="summary-value present" id="present-count">0</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Absent</div>
                <div class="summary-value absent" id="absent-count">0</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total</div>
                <div class="summary-value" id="total-count"><?php echo $totalBeneficiaries; ?></div>
            </div>
        </section>

        <form method="POST" action="AttendanceController.php?action=bulk-record" id="bulkAttendanceForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="session_date" value="<?php echo htmlspecialchars($sessionDate); ?>">

            <section class="attendance-table-card">
                <table class="table prototype-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>PRESENT</th>
                            <th>NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($beneficiaries)): ?>
                            <?php foreach ($beneficiaries as $index => $beneficiary): ?>
                                <?php
                                $beneficiaryCode = sprintf('BEN-%03d', (int)$beneficiary['BeneficiaryID']);
                                $defaultPresent = $index % 2 === 1;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($beneficiaryCode); ?></td>
                                    <td><?php echo htmlspecialchars($beneficiary['FirstName'] . ' ' . $beneficiary['LastName']); ?></td>
                                    <td>
                                        <input type="hidden" name="attendance[<?php echo $index; ?>][beneficiary_id]" value="<?php echo (int)$beneficiary['BeneficiaryID']; ?>">
                                        <input type="hidden" name="attendance[<?php echo $index; ?>][status]" value="<?php echo $defaultPresent ? 'present' : 'absent'; ?>" data-status-input>
                                        <button type="button"
                                                class="present-toggle <?php echo $defaultPresent ? 'yes' : 'no'; ?>"
                                                onclick="toggleAttendance(this)">
                                            <i class="far <?php echo $defaultPresent ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-2" aria-hidden="true"></i>
                                            <span><?php echo $defaultPresent ? 'Yes' : 'No'; ?></span>
                                        </button>
                                    </td>
                                    <td>
                                        <input class="form-control notes-input" type="text"
                                               name="attendance[<?php echo $index; ?>][notes]"
                                               placeholder="Optional notes..." maxlength="255">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">No active beneficiaries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section class="save-card">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2" aria-hidden="true"></i>Save Attendance
                </button>
            </section>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCounters() {
            const statuses = Array.from(document.querySelectorAll('[data-status-input]')).map(input => input.value);
            document.getElementById('present-count').textContent = statuses.filter(status => status === 'present').length;
            document.getElementById('absent-count').textContent = statuses.filter(status => status === 'absent').length;
        }

        function setButton(button, status) {
            const input = button.closest('td').querySelector('[data-status-input]');
            input.value = status;
            button.classList.toggle('yes', status === 'present');
            button.classList.toggle('no', status === 'absent');
            button.querySelector('i').className = status === 'present'
                ? 'far fa-circle-check me-2'
                : 'far fa-circle-xmark me-2';
            button.querySelector('span').textContent = status === 'present' ? 'Yes' : 'No';
        }

        function toggleAttendance(button) {
            const input = button.closest('td').querySelector('[data-status-input]');
            setButton(button, input.value === 'present' ? 'absent' : 'present');
            updateCounters();
        }

        function markAll(status) {
            document.querySelectorAll('.present-toggle').forEach(button => setButton(button, status));
            updateCounters();
        }

        document.getElementById('attendanceDate').addEventListener('change', function () {
            this.form.submit();
        });

        updateCounters();
    </script>
</body>
</html>
