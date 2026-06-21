<?php
$pageTitle = 'Volunteer Availability';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-calendar-alt"></i> Volunteer Availability</h1>
            <p class="mb-0 mt-2">Set your preferred availability days</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="mb-4">
                        <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($volunteer['FullName']); ?></h4>
                        <p class="text-muted">Set your availability for the next scheduling period</p>
                    </div>

                    <form method="POST" action="VolunteerScheduleController.php" id="availability_form">
                        <input type="hidden" name="action" value="save_availability">
                        <input type="hidden" name="volunteer_id" value="<?php echo (int)$volunteer['VolunteerID']; ?>">

                        <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $dayAbbrev = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        ?>

                        <?php foreach ($days as $index => $day): ?>
                            <?php
                                $isAvailable = false;
                                $notes = '';
                                if (!empty($availability)) {
                                    $dayData = array_filter($availability, fn($a) => $a['DayOfWeek'] === $index);
                                    if (!empty($dayData)) {
                                        $dayData = reset($dayData);
                                        $isAvailable = (bool)$dayData['IsAvailable'];
                                        $notes = $dayData['Notes'] ?? '';
                                    }
                                }
                            ?>
                            <div class="availability-row">
                                <div class="day-label"><?php echo $day; ?></div>
                                
                                <div>
                                    <input type="hidden" name="availability[<?php echo $index; ?>][day_of_week]" value="<?php echo $index; ?>">
                                    <div class="availability-toggle">
                                        <button type="button" class="toggle-btn available-toggle" 
                                                data-day="<?php echo $index; ?>"
                                                data-available="1"
                                                <?php echo $isAvailable ? 'style="display: none;"' : ''; ?>>
                                            <i class="fas fa-check"></i> Available
                                        </button>
                                        <button type="button" class="toggle-btn unavailable-toggle"
                                                data-day="<?php echo $index; ?>"
                                                data-available="0"
                                                <?php echo !$isAvailable ? 'style="display: none;"' : ''; ?>>
                                            <i class="fas fa-times"></i> Unavailable
                                        </button>
                                    </div>
                                    <input type="hidden" class="availability-value" name="availability[<?php echo $index; ?>][is_available]" 
                                           value="<?php echo $isAvailable ? '1' : '0'; ?>">
                                </div>

                                <div>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="availability[<?php echo $index; ?>][notes]"
                                           placeholder="Optional notes"
                                           value="<?php echo htmlspecialchars($notes); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Save Availability
                            </button>
                            <a href="VolunteerScheduleController.php?action=list" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.available-toggle, .unavailable-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const day = this.dataset.day;
                const isAvailable = this.dataset.available;
                
                const toggleGroup = this.closest('.availability-toggle');
                const availableBtn = toggleGroup.querySelector('[data-available="1"]');
                const unavailableBtn = toggleGroup.querySelector('[data-available="0"]');
                const valueField = toggleGroup.parentElement.querySelector('.availability-value');
                
                if (isAvailable === '1') {
                    availableBtn.style.display = 'none';
                    unavailableBtn.style.display = 'flex';
                    unavailableBtn.classList.add('active');
                    availableBtn.classList.remove('active');
                } else {
                    availableBtn.style.display = 'flex';
                    unavailableBtn.style.display = 'none';
                    availableBtn.classList.add('active');
                    unavailableBtn.classList.remove('active');
                }
                
                valueField.value = isAvailable;
            });
        });

        // Initialize button states
        document.querySelectorAll('.availability-value').forEach(field => {
            const isAvailable = field.value === '1';
            const toggleGroup = field.closest('.availability-row').querySelector('.availability-toggle');
            const activeBtn = isAvailable ? 
                toggleGroup.querySelector('[data-available="1"]') :
                toggleGroup.querySelector('[data-available="0"]');
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
        });
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
