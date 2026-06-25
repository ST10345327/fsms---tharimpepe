<?php $pageTitle = 'Reports'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .reports-page { padding: 30px; }
        .reports-layout {
            display: grid;
            gap: 30px;
            grid-template-columns: 0.9fr 1.85fr;
        }
        .report-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }
        .report-card-body { padding: 30px; }
        .report-card h2 {
            color: #071326;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 26px;
        }
        .report-control label {
            color: #1f2a44;
            display: block;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .report-control { margin-bottom: 24px; }
        .report-control .form-control,
        .report-control .form-select {
            border-radius: 10px;
            color: #1b3a5c;
            font-size: 20px;
            min-height: 52px;
        }
        .report-btn {
            border: 0;
            border-radius: 10px;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            min-height: 50px;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
            line-height: 50px;
        }
        .report-btn:hover { opacity: 0.9; color: #fff; }
        .report-btn.navy { background: #1b3a5c; }
        .report-btn.red { background: #f00013; }
        .report-btn.green { background: #00b341; }
        .export-card { margin-top: 30px; }
        .export-card .report-btn { margin-top: 14px; }
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .nav-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
            text-decoration: none;
            color: #1f2a44;
            display: block;
        }
        .nav-item:hover {
            background: #1b3a5c;
            border-color: #1b3a5c;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(27, 58, 92, 0.2);
        }
        .nav-item i { font-size: 28px; margin-bottom: 8px; display: block; }
        .nav-item span { font-size: 14px; font-weight: 600; }
        .preview-head {
            align-items: center;
            border-bottom: 1px solid #dfe3e8;
            display: flex;
            justify-content: space-between;
            padding: 24px 20px;
        }
        .preview-head h2 { margin: 0; font-size: 20px; }
        .preview-body { padding: 30px; }
        @media (max-width: 1100px) { .reports-layout { grid-template-columns: 1fr; } .nav-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid reports-page">
        <div class="reports-layout">
            <aside>
                <section class="report-card">
                    <div class="report-card-body">
                        <h2>Report Generator</h2>
                        <form id="reportGenerator">
                            <div class="report-control">
                                <label for="reportType">Report Type</label>
                                <select id="reportType" class="form-select">
                                    <option value="attendance">Attendance Report</option>
                                    <option value="donations">Donation Report</option>
                                    <option value="food_stock">Stock Report</option>
                                    <option value="program_summary">Impact Report</option>
                                </select>
                            </div>
                            <div class="report-control">
                                <label for="startDate">Start Date</label>
                                <input id="startDate" class="form-control" type="date">
                            </div>
                            <div class="report-control">
                                <label for="endDate">End Date</label>
                                <input id="endDate" class="form-control" type="date">
                            </div>
                            <button class="report-btn navy" type="submit">Generate Report</button>
                        </form>
                    </div>
                </section>

                <section class="report-card export-card">
                    <div class="report-card-body">
                        <h2>Export Options</h2>
                        <a href="ReportsController.php?action=export&report=attendance" class="report-btn red">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a>
                        <a href="ReportsController.php?action=export_xls&report=attendance" class="report-btn navy">
                            <i class="fas fa-file-excel me-2"></i>XLS
                        </a>
                        <button class="report-btn green" type="button" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </section>
            </aside>

            <section class="report-card">
                <div class="preview-head">
                    <h2>Available Reports</h2>
                </div>
                <div class="preview-body">
                    <div class="nav-grid">
                        <a href="ReportsController.php?action=attendance" class="nav-item">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Attendance Report</span>
                        </a>
                        <a href="ReportsController.php?action=donations" class="nav-item">
                            <i class="fas fa-gift"></i>
                            <span>Donation Report</span>
                        </a>
                        <a href="ReportsController.php?action=volunteer_performance" class="nav-item">
                            <i class="fas fa-star"></i>
                            <span>Volunteer Performance</span>
                        </a>
                        <a href="ReportsController.php?action=volunteer_schedule" class="nav-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Volunteer Schedule</span>
                        </a>
                        <a href="ReportsController.php?action=food_stock" class="nav-item">
                            <i class="fas fa-boxes"></i>
                            <span>Food Stock Report</span>
                        </a>
                        <a href="ReportsController.php?action=food_distribution" class="nav-item">
                            <i class="fas fa-box"></i>
                            <span>Food Distribution</span>
                        </a>
                        <a href="ReportsController.php?action=beneficiaries" class="nav-item">
                            <i class="fas fa-people-arrows"></i>
                            <span>Beneficiary Report</span>
                        </a>
                        <a href="ReportsController.php?action=audit" class="nav-item">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Activity Audit</span>
                        </a>
                        <a href="ReportsController.php?action=program_summary" class="nav-item">
                            <i class="fas fa-chart-pie"></i>
                            <span>Program Summary</span>
                        </a>
                        <a href="ReportsController.php?action=financial_summary" class="nav-item">
                            <i class="fas fa-chart-bar"></i>
                            <span>Financial Summary</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        document.getElementById('reportGenerator').addEventListener('submit', function(e) {
            e.preventDefault();
            var type = document.getElementById('reportType').value;
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;
            var url = 'ReportsController.php?action=' + encodeURIComponent(type);
            if (startDate) url += '&from_date=' + encodeURIComponent(startDate);
            if (endDate) url += '&to_date=' + encodeURIComponent(endDate);
            window.location.href = url;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
