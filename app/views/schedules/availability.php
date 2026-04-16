<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Availability - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .availability-row {
            display: grid;
            grid-template-columns: 100px 1fr 200px;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }
        .availability-row:last-child {
            border-bottom: none;
        }
        .day-label {
            font-weight: 600;
            color: #667eea;
        }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
        .availability-toggle {
            display: flex;
            gap: 10px;
        }
        .toggle-btn {
            flex: 1;
            padding: 8px 10px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .toggle-btn.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-calendar-alt"></i> Volunteer Availability</h1>
            <p class="mb-0 mt-2">Set your preferred availability days</p>
        </div>
    </div>

    <!-- Main Content -->
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
</body>
</html>
