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
        }
        .report-btn.navy { background: #1b3a5c; }
        .report-btn.red { background: #f00013; }
        .report-btn.green { background: #00b341; }
        .report-btn.gray { background: #4b5563; }
        .export-card { margin-top: 30px; }
        .export-card .report-btn { margin-top: 14px; }
        .preview-head {
            align-items: center;
            border-bottom: 1px solid #dfe3e8;
            display: flex;
            justify-content: space-between;
            padding: 24px 20px;
        }
        .preview-head h2 { margin: 0; }
        .preview-body { padding: 48px 40px; }
        .preview-title { text-align: center; }
        .preview-title h1 { font-size: 30px; font-weight: 700; margin-bottom: 12px; }
        .preview-title p { color: #475569; font-size: 20px; margin-bottom: 12px; }
        .preview-rule { border-top: 1px solid #dfe3e8; margin: 30px 0 40px; }
        .report-metrics {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 34px;
        }
        .report-metric {
            border-radius: 10px;
            padding: 22px;
            text-align: center;
        }
        .report-metric.blue { background: #eff6ff; color: #005cff; }
        .report-metric.green { background: #f0fdf4; color: #00a33a; }
        .report-metric.purple { background: #faf5ff; color: #8f00ff; }
        .report-metric span { color: #334155; display: block; font-size: 16px; margin-bottom: 8px; }
        .report-metric strong { font-size: 30px; font-weight: 400; }
        .preview-table th { background: #f8fafc; }
        .preview-table th, .preview-table td { border-color: #e5e7eb; font-size: 18px; padding: 12px 20px; }
        @media (max-width: 1100px) { .reports-layout, .report-metrics { grid-template-columns: 1fr; } }
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
                        <div class="report-control">
                            <label for="reportType">Report Type</label>
                            <select id="reportType" class="form-select">
                                <option>Attendance Report</option>
                                <option>Donation Report</option>
                                <option>Stock Report</option>
                                <option>Impact Report</option>
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
                        <button class="report-btn navy" type="button">Generate Report</button>
                    </div>
                </section>

                <section class="report-card export-card">
                    <div class="report-card-body">
                        <h2>Export Options</h2>
                        <button class="report-btn red" type="button"><i class="far fa-file-pdf me-2"></i>Export PDF</button>
                        <button class="report-btn green" type="button"><i class="far fa-file-excel me-2"></i>Export Excel</button>
                        <button class="report-btn gray" type="button"><i class="fas fa-print me-2"></i>Print</button>
                    </div>
                </section>
            </aside>

            <section class="report-card">
                <div class="preview-head">
                    <h2>Report Preview</h2>
                    <i class="fas fa-download" aria-hidden="true"></i>
                </div>
                <div class="preview-body">
                    <div class="preview-title">
                        <h1>Tharimpepe Feeding Scheme</h1>
                        <p>Attendance Report</p>
                        <p class="fs-6">Generated on: <?php echo date('Y/m/d'); ?></p>
                    </div>
                    <div class="preview-rule"></div>
                    <div class="report-metrics">
                        <div class="report-metric blue"><span>Total Days</span><strong>30</strong></div>
                        <div class="report-metric green"><span>Total Meals</span><strong>3,845</strong></div>
                        <div class="report-metric purple"><span>Avg Daily</span><strong>128</strong></div>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Daily Breakdown</h3>
                    <table class="table preview-table">
                        <thead>
                            <tr><th>Date</th><th>Present</th><th>Absent</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php for ($day = 1; $day <= 5; $day++): ?>
                                <tr>
                                    <td>2026-04-0<?php echo $day; ?></td>
                                    <td>125</td>
                                    <td>17</td>
                                    <td>142</td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
