<?php $pageTitle = 'Dashboard Overview';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
            <p class="mb-0 mt-2">System-wide analytics and key performance indicators</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <!-- Analytics Navigation -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="analytics-nav">
                    <a href="DashboardController.php?action=overview" class="active">
                        <i class="fas fa-chart-line"></i> Overview
                    </a>
                    <a href="DashboardController.php?action=feeding">
                        <i class="fas fa-utensils"></i> Feeding Program
                    </a>
                    <a href="DashboardController.php?action=volunteers">
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
                            <div class="stat-label"><i class="fas fa-users"></i> Active Volunteers</div>
                            <div class="stat-value"><?php echo (int)$systemStats['active_volunteers']; ?></div>
                            <small class="text-muted">of <?php echo (int)$systemStats['total_volunteers']; ?> total</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label"><i class="fas fa-user-friends"></i> Active Beneficiaries</div>
                            <div class="stat-value"><?php echo (int)$systemStats['active_beneficiaries']; ?></div>
                            <small class="text-muted">of <?php echo (int)$systemStats['total_beneficiaries']; ?> total</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label"><i class="fas fa-calendar"></i> Today's Shifts</div>
                            <div class="stat-value"><?php echo (int)$schedulingStats['today_shifts']; ?></div>
                            <small class="text-muted"><?php echo date('M d, Y'); ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-label"><i class="fas fa-check-circle"></i> Today's Attendance</div>
                            <div class="stat-value"><?php echo (int)$feedingStats['today_attendance']; ?></div>
                            <small class="text-muted">beneficiaries served</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="metric-card">
                    <h5 class="mb-3"><i class="fas fa-tachometer-alt"></i> Key Performance Indicators</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <span class="kpi-badge kpi-blue">Avg Attendance</span>
                                <div style="font-size: 2rem; font-weight: 700; margin-top: 10px; color: #667eea;">
                                    <?php echo $kpis['avg_attendance_per_session']; ?>
                                </div>
                                <small class="text-muted">per session</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <span class="kpi-badge kpi-green">Volunteer Utilization</span>
                                <div style="font-size: 2rem; font-weight: 700; margin-top: 10px; color: #28a745;">
                                    <?php echo $kpis['volunteer_utilization_rate']; ?>%
                                </div>
                                <small class="text-muted">completion rate</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <span class="kpi-badge kpi-orange">Stock Value</span>
                                <div style="font-size: 2rem; font-weight: 700; margin-top: 10px; color: #fd7e14;">
                                    R<?php echo number_format($foodStockStatus['total_stock_value'], 0); ?>
                                </div>
                                <small class="text-muted">total inventory</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <span class="kpi-badge kpi-green">Monthly Donations</span>
                                <div style="font-size: 2rem; font-weight: 700; margin-top: 10px; color: #28a745;">
                                    R<?php echo number_format($donationStats['monthly_donations_amount'], 0); ?>
                                </div>
                                <small class="text-muted"><?php echo $donationStats['monthly_donations_count']; ?> donations</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="mb-3">Beneficiary Trend (30 Days)</h5>
                    <canvas id="beneficiaryChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="mb-3">Attendance by Role</h5>
                    <canvas id="roleChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-lg-6">
                <div class="metric-card">
                    <h5 class="mb-3"><i class="fas fa-clock"></i> Recent Activities</h5>
                    <div>
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-type">
                                            <?php echo htmlspecialchars($activity['ActivityType']); ?>
                                            <small class="text-muted">by <?php echo htmlspecialchars($activity['username']); ?></small>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('M d, Y H:i', strtotime($activity['Timestamp'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Donors -->
            <div class="col-lg-6">
                <div class="metric-card">
                    <h5 class="mb-3"><i class="fas fa-gift"></i> Top Donors</h5>
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Donor</th>
                                <th>Donations</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topDonors)): ?>
                                <?php foreach ($topDonors as $donor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donor['DonorName']); ?></td>
                                        <td><?php echo (int)$donor['donation_count']; ?></td>
                                        <td><strong>R<?php echo number_format((float)$donor['total_donated'], 0); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-muted text-center py-3">No donation data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Beneficiary Trend Chart
        const beneficiaryTrend = <?php echo json_encode($beneficiaryTrend); ?>;
        const beneficiaryLabels = beneficiaryTrend.map(d => new Date(d.date).toLocaleDateString('en', { month: 'short', day: 'numeric' }));
        const beneficiaryData = beneficiaryTrend.map(d => d.beneficiary_count);

        const beneficiaryCtx = document.getElementById('beneficiaryChart').getContext('2d');
        new Chart(beneficiaryCtx, {
            type: 'line',
            data: {
                labels: beneficiaryLabels,
                datasets: [{
                    label: 'Beneficiaries Served',
                    data: beneficiaryData,
                    borderColor: '#2E7D32',
                    backgroundColor: 'rgba(46, 125, 50, 0.12)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2E7D32',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Attendance by Role Chart
        const roleData = <?php echo json_encode($attendanceByRole); ?>;
        const roleLabels = roleData.map(r => r.Role);
        const roleCounts = roleData.map(r => r.count);

        const roleCtx = document.getElementById('roleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: roleLabels,
                datasets: [{
                    data: roleCounts,
                    backgroundColor: ['#1B3A5C', '#19A7A8', '#2E7D32', '#6D5DFC', '#F97316'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
