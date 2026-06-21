<?php
$pageTitle = 'Volunteer Analytics';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-users"></i> Volunteer Analytics</h1>
            <p class="mb-0 mt-2">Volunteer performance and scheduling statistics</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <!-- Analytics Navigation -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="analytics-nav">
                    <a href="DashboardController.php?action=overview">
                        <i class="fas fa-chart-line"></i> Overview
                    </a>
                    <a href="DashboardController.php?action=feeding">
                        <i class="fas fa-utensils"></i> Feeding Program
                    </a>
                    <a href="DashboardController.php?action=volunteers" class="active">
                        <i class="fas fa-users"></i> Volunteer Performance
                    </a>
                    <a href="DashboardController.php?action=donations">
                        <i class="fas fa-gift"></i> Donations
                    </a>
                    <a href="DashboardController.php?action=inventory">
                        <i class="fas fa-boxes"></i> Inventory
                    </a>
                    <a href="DashboardController.php?action=kpis">
                        <i class="fas fa-tachometer-alt"></i> KPIs
                    </a>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="col-md-9">
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-users"></i> Active Volunteers</div>
                            <div class="stat-value"><?php echo (int)$systemStats['active_volunteers']; ?></div>
                            <small class="text-muted">of <?php echo (int)$systemStats['total_volunteers']; ?> total</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-calendar"></i> Total Shifts</div>
                            <div class="stat-value"><?php echo (int)$schedulingStats['total_shifts']; ?></div>
                            <small class="text-muted">all time</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-check-circle"></i> Completed</div>
                            <div class="stat-value"><?php echo (int)$schedulingStats['completed_schedules']; ?></div>
                            <small class="text-muted">this month</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-clock"></i> Volunteer Hours</div>
                            <div class="stat-value"><?php echo number_format($schedulingStats['volunteer_hours_month'], 1); ?></div>
                            <small class="text-muted">this month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers Table -->
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-crown"></i> Top Volunteers by Hours</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Volunteer Name</th>
                            <th>Total Shifts</th>
                            <th>Completed</th>
                            <th>Total Hours</th>
                            <th>Avg Hours/Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($volunteerPerformance)): ?>
                            <?php foreach ($volunteerPerformance as $volunteer): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($volunteer['FullName']); ?></strong></td>
                                    <td><?php echo (int)$volunteer['total_shifts']; ?></td>
                                    <td><?php echo (int)$volunteer['completed_shifts']; ?></td>
                                    <td><?php echo number_format((float)(isset($volunteer['total_hours']) ? $volunteer['total_hours'] : 0), 1); ?></td>
                                    <td>
                                        <?php 
                                            $completed = (int)$volunteer['completed_shifts'];
                                            $hours = (float)(isset($volunteer['total_hours']) ? $volunteer['total_hours'] : 0);
                                            $avg = $completed > 0 ? $hours / $completed : 0;
                                            echo number_format($avg, 1);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No volunteer data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="DashboardController.php?action=overview" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
