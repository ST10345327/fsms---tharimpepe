<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Donors - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .donor-rank {
            display: flex;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #28a745;
        }
        .rank-badge {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%); }
        .rank-other { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .donor-info {
            flex-grow: 1;
        }
        .donor-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .donor-details {
            color: #666;
            font-size: 0.9rem;
        }
        .donor-stats {
            text-align: right;
        }
        .stat-item {
            margin-bottom: 8px;
        }
        .stat-label {
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
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
        .action-buttons {
            display: flex;
            gap: 10px;
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
                    <h1><i class="fas fa-crown"></i> Top Donors</h1>
                    <p class="mb-0 mt-2">Recognition and ranking of major contributors</p>
                </div>
                <a href="DonationController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Ranking Period Filter -->
        <div class="content-card mb-4">
            <div class="row">
                <div class="col-md-6">
                    <h5>Ranking by Period</h5>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" action="DonationController.php" class="d-flex gap-2">
                        <input type="hidden" name="action" value="top_donors">
                        <select class="form-select form-select-sm w-auto" name="period" onchange="this.form.submit()">
                            <option value="all" <?php echo ($_GET['period'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Time</option>
                            <option value="year" <?php echo ($_GET['period'] ?? '') === 'year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="month" <?php echo ($_GET['period'] ?? '') === 'month' ? 'selected' : ''; ?>>This Month</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Top Donors Ranking -->
        <h4 class="mb-3">Top Contributors</h4>
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($topDonors)): ?>
                    <?php foreach ($topDonors as $index => $donor): ?>
                        <div class="donor-rank">
                            <div class="rank-badge rank-<?php echo $index <= 2 ? ($index + 1) : 'other'; ?>">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="donor-info">
                                <div class="donor-name"><?php echo htmlspecialchars($donor['DonorName']); ?></div>
                                <div class="donor-details">
                                    <?php if (!empty($donor['DonorEmail'])): ?>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($donor['DonorEmail']); ?> &nbsp;|&nbsp;
                                    <?php endif; ?>
                                    <i class="fas fa-gift"></i> <?php echo $donor['donation_count']; ?> donation<?php echo $donor['donation_count'] != 1 ? 's' : ''; ?>
                                </div>
                            </div>
                            <div class="donor-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Total Donated</div>
                                    <div class="stat-value">R<?php echo number_format((float)$donor['total_amount'], 2); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Average Gift</div>
                                    <div class="stat-value">R<?php echo number_format((float)$donor['average_amount'], 2); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center mb-0">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <strong>No donation records found.</strong>
                        <p class="mb-0">Start recording donations to see top donors.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Summary Statistics -->
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-4"><i class="fas fa-chart-line"></i> Summary</h5>
                    
                    <div class="mb-3">
                        <div class="text-muted small text-uppercase">Total Active Donors</div>
                        <div class="display-6 fw-bold text-success"><?php echo count($topDonors) ?? 0; ?></div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="text-muted small text-uppercase">Total Donations</div>
                        <div class="display-6 fw-bold text-success"><?php echo $summary['total_donations'] ?? 0; ?></div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="text-muted small text-uppercase">Total Amount Raised</div>
                        <div class="display-6 fw-bold text-success">R<?php echo number_format($summary['total_amount'] ?? 0, 2); ?></div>
                    </div>

                    <hr>

                    <div>
                        <div class="text-muted small text-uppercase">Average Donor Gift</div>
                        <div class="display-6 fw-bold text-success">R<?php echo number_format($summary['average_gift'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Donor Breakdown (Table View) -->
        <h4 class="mt-5 mb-3">Detailed Donor Information</h4>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Donor Name</th>
                            <th>Email</th>
                            <th>Total Donated</th>
                            <th>Donations</th>
                            <th>Avg. Gift</th>
                            <th>First Donation</th>
                            <th>Latest Donation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topDonors)): ?>
                            <?php foreach ($topDonors as $index => $donor): ?>
                                <tr>
                                    <td>
                                        <span class="badge rank-badge rank-<?php echo $index <= 2 ? ($index + 1) : 'other'; ?>" style="width: auto; padding: 0.35rem 0.65rem;">
                                            #<?php echo $index + 1; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($donor['DonorName']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($donor['DonorEmail'] ?? 'N/A'); ?></td>
                                    <td><strong>R<?php echo number_format((float)$donor['total_amount'], 2); ?></strong></td>
                                    <td><?php echo $donor['donation_count']; ?></td>
                                    <td>R<?php echo number_format((float)$donor['average_amount'], 2); ?></td>
                                    <td><?php echo isset($donor['first_donation']) ? date('M d, Y', strtotime($donor['first_donation'])) : 'N/A'; ?></td>
                                    <td><?php echo isset($donor['latest_donation']) ? date('M d, Y', strtotime($donor['latest_donation'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons justify-content-center">
            <a href="DonationController.php?action=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Record New Donation
            </a>
            <a href="DonationController.php?action=report" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
