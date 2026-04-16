<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Report - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            position: relative;
            height: 350px;
        }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-gift"></i> Donation Report</h1>
            <p class="mb-0 mt-2">Track donations and analyze donor contributions</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Donations</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="donations">
                
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="fas fa-filter"></i> Filter Report
                    </button>
                    <a href="ReportsController.php?action=donations" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Donation Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count($donationData ?? []); ?>
                    </div>
                    <div class="text-muted">Total Donations</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #28a745; font-size: 2.5rem; font-weight: 700;">
                        ZWL<?php echo number_format(array_sum(array_column($donationData ?? [], 'Amount')), 0); ?>
                    </div>
                    <div class="text-muted">Total Amount</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="color: #667eea; font-size: 2.5rem; font-weight: 700;">
                        <?php echo count($donorSummary ?? []); ?>
                    </div>
                    <div class="text-muted">Unique Donors</div>
                </div>
            </div>
        </div>

        <!-- Top Donors Table -->
        <div class="table-card">
            <h5 class="mb-3">Top Donors</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Donor Name</th>
                            <th>Donation Count</th>
                            <th>Total Amount</th>
                            <th>Last Donation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($donorSummary)): ?>
                            <?php foreach (array_slice($donorSummary, 0, 10) as $donor): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($donor['DonorName']); ?></strong></td>
                                    <td><?php echo (int)$donor['donation_count']; ?></td>
                                    <td>ZWL<?php echo number_format((float)$donor['total_amount'], 0); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($donor['last_donation'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No donor data found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Donations -->
        <div class="table-card">
            <h5 class="mb-3">Donation Details</h5>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Donor</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($donationData)): ?>
                            <?php foreach ($donationData as $donation): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($donation['DonationDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($donation['DonorName']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['DonationType']); ?></td>
                                    <td><strong>ZWL<?php echo number_format((float)$donation['Amount'], 0); ?></strong></td>
                                    <td><?php echo htmlspecialchars($donation['Notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No donation records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="ReportsController.php?action=dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
