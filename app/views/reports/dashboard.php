<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FSMS</title>
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
        .report-category {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .report-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        .report-item:last-child {
            border-bottom: none;
        }
        .report-item:hover {
            background: #f8f9fa;
        }
        .report-icon {
            font-size: 1.5rem;
            color: #667eea;
            min-width: 30px;
        }
        .report-info {
            flex: 1;
        }
        .report-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .report-description {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        .report-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .report-link:hover {
            color: inherit;
        }
        .btn-report {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-report:hover {
            background: linear-gradient(135deg, #5568d3 0%, #693a90 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-file-alt"></i> Reports & Analytics</h1>
            <p class="mb-0 mt-2">Access comprehensive system reports and data analysis</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Feeding Program Reports -->
        <div class="report-category">
            <h4 class="mb-3"><i class="fas fa-utensils"></i> Feeding Program Reports</h4>
            
            <a href="ReportsController.php?action=attendance" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="report-info">
                        <div class="report-name">Attendance Report</div>
                        <p class="report-description">View detailed attendance records by date and beneficiary</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>

            <a href="ReportsController.php?action=beneficiaries" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-users"></i></div>
                    <div class="report-info">
                        <div class="report-name">Beneficiary Report</div>
                        <p class="report-description">List all beneficiaries with filter options by role and status</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>
        </div>

        <!-- Volunteer Reports -->
        <div class="report-category">
            <h4 class="mb-3"><i class="fas fa-calendar"></i> Volunteer Reports</h4>
            
            <a href="ReportsController.php?action=volunteer_performance" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-star"></i></div>
                    <div class="report-info">
                        <div class="report-name">Volunteer Performance Report</div>
                        <p class="report-description">Analyze volunteer hours, completion rates, and performance metrics</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>

            <a href="ReportsController.php?action=volunteer_schedule" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="report-info">
                        <div class="report-name">Volunteer Schedule Report</div>
                        <p class="report-description">View volunteer schedules with filtering by date and status</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>
        </div>

        <!-- Donation Reports -->
        <div class="report-category">
            <h4 class="mb-3"><i class="fas fa-gift"></i> Donation & Funding Reports</h4>
            
            <a href="ReportsController.php?action=donations" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-money-bill"></i></div>
                    <div class="report-info">
                        <div class="report-name">Donation Report</div>
                        <p class="report-description">Track donations by date, type, and donor with summary statistics</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>

            <a href="ReportsController.php?action=financial_summary" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="report-info">
                        <div class="report-name">Financial Summary Report</div>
                        <p class="report-description">Monthly financial overview with donations and expenditures</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>
        </div>

        <!-- Inventory Reports -->
        <div class="report-category">
            <h4 class="mb-3"><i class="fas fa-boxes"></i> Inventory Reports</h4>
            
            <a href="ReportsController.php?action=food_stock" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-box"></i></div>
                    <div class="report-info">
                        <div class="report-name">Food Stock Report</div>
                        <p class="report-description">Current inventory status with stock levels and expiry dates</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>

            <a href="ReportsController.php?action=food_distribution" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-truck"></i></div>
                    <div class="report-info">
                        <div class="report-name">Food Distribution Report</div>
                        <p class="report-description">Track food distribution history with location and purpose</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>
        </div>

        <!-- System Reports -->
        <div class="report-category">
            <h4 class="mb-3"><i class="fas fa-cog"></i> System & Audit Reports</h4>
            
            <a href="ReportsController.php?action=program_summary" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="report-info">
                        <div class="report-name">Program Summary Report</div>
                        <p class="report-description">Comprehensive overview of all program activities and statistics</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>

            <a href="ReportsController.php?action=audit" class="report-link">
                <div class="report-item">
                    <div class="report-icon"><i class="fas fa-history"></i></div>
                    <div class="report-info">
                        <div class="report-name">Activity Audit Report</div>
                        <p class="report-description">System activity log with user actions and timestamps for compliance</p>
                    </div>
                    <div><i class="fas fa-arrow-right" style="color: #667eea;"></i></div>
                </div>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="text-center mt-4">
            <a href="../../views/dashboard.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
