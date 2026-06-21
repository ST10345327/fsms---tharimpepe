<?php
$pageTitle = 'Attendance';
$totalBeneficiaries = count($beneficiaries);
require_once __DIR__ . "/../includes/layout-header.php";
?>

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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
