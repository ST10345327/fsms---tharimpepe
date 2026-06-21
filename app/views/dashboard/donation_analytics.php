<?php
$pageTitle = 'Donation Analytics';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-gift"></i> Donation Analytics</h1>
            <p class="mb-0 mt-2">Donor contributions and funding trends</p>
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
                    <a href="DashboardController.php?action=volunteers">
                        <i class="fas fa-users"></i> Volunteer Performance
                    </a>
                    <a href="DashboardController.php?action=donations" class="active">
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
                            <div><i class="fas fa-users"></i> Total Donors</div>
                            <div class="stat-value"><?php echo (int)$donationStats['total_donors']; ?></div>
                            <small class="text-muted">registered</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-calendar"></i> Monthly Donations</div>
                            <div class="stat-value"><?php echo (int)$donationStats['monthly_donations_count']; ?></div>
                            <small class="text-muted">this month</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-money-bill"></i> Monthly Total</div>
                            <div class="stat-value">ZWL<?php echo number_format($donationStats['monthly_donations_amount'], 0); ?></div>
                            <small class="text-muted">this month</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <div><i class="fas fa-calendar-alt"></i> Yearly Total</div>
                            <div class="stat-value">ZWL<?php echo number_format($donationStats['yearly_donations'], 0); ?></div>
                            <small class="text-muted">this year</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h5 class="mb-3">Donations by Type</h5>
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="table-container">
                    <h5 class="mb-3">Donation Source Distribution</h5>
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($donationSources)): ?>
                                <?php foreach ($donationSources as $source): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($source['DonationType']); ?></td>
                                        <td><?php echo (int)$source['count']; ?></td>
                                        <td><strong>ZWL<?php echo number_format((float)$source['total_amount'], 0); ?></strong></td>
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

        <!-- Top Donors Table -->
        <div class="table-container">
            <h5 class="mb-3"><i class="fas fa-crown"></i> Top Donors</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Donor Name</th>
                            <th>Donation Count</th>
                            <th>Total Amount</th>
                            <th>Avg Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topDonors)): ?>
                            <?php foreach ($topDonors as $donor): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($donor['DonorName']); ?></strong></td>
                                    <td><?php echo (int)$donor['donation_count']; ?></td>
                                    <td>ZWL<?php echo number_format((float)$donor['total_donated'], 0); ?></td>
                                    <td>ZWL<?php echo number_format((float)($donor['total_donated'] / max($donor['donation_count'], 1)), 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No donor data available</td>
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sourceData = <?php echo json_encode($donationSources); ?>;
        const sourceLabels = sourceData.map(s => s.DonationType);
        const sourceAmounts = sourceData.map(s => s.total_amount);

        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'pie',
            data: {
                labels: sourceLabels,
                datasets: [{
                    data: sourceAmounts,
                    backgroundColor: ['#667eea', '#764ba2', '#28a745', '#17a2b8', '#fd7e14'],
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
