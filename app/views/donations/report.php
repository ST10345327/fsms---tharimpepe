<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Reports - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            position: relative;
            height: 400px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table thead {
            background: #f8f9fa;
        }
        .badge-cash { background: #28a745 !important; }
        .badge-food { background: #fd7e14 !important; }
        .badge-supplies { background: #17a2b8 !important; }
        .badge-other { background: #6c757d !important; }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #218838 0%, #1aa085 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Donation Reports & Analytics</h1>
                    <p class="mb-0 mt-2">Comprehensive donation tracking and analysis</p>
                </div>
                <a href="DonationController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Section -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Reports</h5>
            <form method="GET" action="DonationController.php" class="row g-3">
                <input type="hidden" name="action" value="report">
                
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Donation Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="cash" <?php echo ($_GET['type'] ?? '') === 'cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="food" <?php echo ($_GET['type'] ?? '') === 'food' ? 'selected' : ''; ?>>Food</option>
                        <option value="supplies" <?php echo ($_GET['type'] ?? '') === 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                        <option value="other" <?php echo ($_GET['type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Section -->
        <h4 class="mb-3">Key Statistics</h4>
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <i class="fas fa-gift fa-3x" style="color: #28a745;"></i>
                    <div class="stat-label">Total Donations</div>
                    <div class="stat-value"><?php echo $stats['total_donations'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave fa-3x" style="color: #fd7e14;"></i>
                    <div class="stat-label">Total Cash Donated</div>
                    <div class="stat-value">R<?php echo number_format($stats['total_cash'] ?? 0, 2); ?></div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <i class="fas fa-user-friends fa-3x" style="color: #17a2b8;"></i>
                    <div class="stat-label">Unique Donors</div>
                    <div class="stat-value"><?php echo $stats['unique_donors'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <i class="fas fa-calculator fa-3x" style="color: #6c757d;"></i>
                    <div class="stat-label">Average Donation</div>
                    <div class="stat-value">R<?php echo number_format($stats['average_donation'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-5">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h6 class="mb-3">Donations by Type</h6>
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-container">
                    <h6 class="mb-3">Donation Trend (Last 30 Days)</h6>
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Donation List Section -->
        <h4 class="mb-3">Donation Details</h4>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Donor Name</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($donations)): ?>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($donation['DonationDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($donation['DonorName']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $donation['DonationType']; ?>">
                                            <?php echo ucfirst($donation['DonationType']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((float)$donation['Amount'] > 0): ?>
                                            <strong>R<?php echo number_format((float)$donation['Amount'], 2); ?></strong>
                                        <?php else: ?>
                                            <em class="text-muted">N/A</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($donation['Description'] ?? '', 0, 50)); ?></td>
                                    <td>
                                        <a href="DonationController.php?action=view&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No donations found for the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Section -->
        <div class="action-buttons">
            <a href="DonationController.php?action=report&export=csv<?php echo isset($_GET['from_date']) ? '&from_date=' . htmlspecialchars($_GET['from_date']) : ''; ?><?php echo isset($_GET['to_date']) ? '&to_date=' . htmlspecialchars($_GET['to_date']) : ''; ?><?php echo isset($_GET['type']) ? '&type=' . htmlspecialchars($_GET['type']) : ''; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Export to CSV
            </a>
            <a href="DonationController.php?action=report&export=pdf<?php echo isset($_GET['from_date']) ? '&from_date=' . htmlspecialchars($_GET['from_date']) : ''; ?><?php echo isset($_GET['to_date']) ? '&to_date=' . htmlspecialchars($_GET['to_date']) : ''; ?><?php echo isset($_GET['type']) ? '&type=' . htmlspecialchars($_GET['type']) : ''; ?>" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf"></i> Export to PDF
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Donation Type Chart
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Food', 'Supplies', 'Other'],
                datasets: [{
                    data: [
                        <?php echo $stats['by_type']['cash'] ?? 0; ?>,
                        <?php echo $stats['by_type']['food'] ?? 0; ?>,
                        <?php echo $stats['by_type']['supplies'] ?? 0; ?>,
                        <?php echo $stats['by_type']['other'] ?? 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#fd7e14', '#17a2b8', '#6c757d']
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

        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_labels ?? []); ?>,
                datasets: [{
                    label: 'Daily Donations (R)',
                    data: <?php echo json_encode($trend_values ?? []); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => 'R' + v } }
                }
            }
        });
    </script>
</body>
</html>
