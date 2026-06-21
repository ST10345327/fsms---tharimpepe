<?php $pageTitle = 'Reports'; require_once __DIR__ . "/../includes/layout-header.php"; ?>
<style>
    .reports-layout {
        display: grid;
        gap: 24px;
        grid-template-columns: 0.9fr 1.85fr;
    }
    .report-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
        overflow: hidden;
    }
    .report-card-body { padding: 24px; }
    .report-card h2 { font-size: 20px; font-weight: 700; margin-bottom: 20px; }
    .report-control label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #1f2a44; }
    .report-control { margin-bottom: 20px; }
    .report-control .form-control,
    .report-control .form-select { border-radius: 10px; font-size: 16px; min-height: 48px; }
    .report-btn {
        border: 0;
        border-radius: 10px;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        min-height: 46px;
        width: 100%;
        margin-top: 8px;
    }
    .report-btn.navy { background: #1b3a5c; }
    .report-btn.red { background: #dc2626; }
    .report-btn.green { background: #16a34a; }
    .report-btn.gray { background: #4b5563; }
    .preview-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #dfe3e8;
    }
    .preview-head h2 { margin: 0; font-size: 20px; }
    .preview-body { padding: 32px 24px; }
    .preview-title { text-align: center; }
    .preview-title h1 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
    .preview-title p { color: #475569; font-size: 16px; margin-bottom: 8px; }
    .preview-rule { border-top: 1px solid #dfe3e8; margin: 24px 0 32px; }
    .report-metrics {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(3, 1fr);
        margin-bottom: 28px;
    }
    .report-metric {
        border-radius: 10px;
        padding: 18px;
        text-align: center;
    }
    .report-metric.blue { background: #eff6ff; color: #005cff; }
    .report-metric.green { background: #f0fdf4; color: #16a34a; }
    .report-metric.purple { background: #faf5ff; color: #8f00ff; }
    .report-metric span { color: #334155; display: block; font-size: 14px; margin-bottom: 6px; }
    .report-metric strong { font-size: 26px; font-weight: 400; }
    .preview-table th { background: #f8fafc; }
    .preview-table th, .preview-table td { border-color: #e5e7eb; font-size: 15px; padding: 10px 16px; }
    @media (max-width: 1023px) { .reports-layout, .report-metrics { grid-template-columns: 1fr; } }
</style>

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
        <section class="report-card" style="margin-top:20px;">
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
                <p class="fs-6 text-muted">Generated on: <?php echo date('Y/m/d'); ?></p>
            </div>
            <div class="preview-rule"></div>
            <div class="report-metrics">
                <div class="report-metric blue"><span>Total Days</span><strong>30</strong></div>
                <div class="report-metric green"><span>Total Meals</span><strong>3,845</strong></div>
                <div class="report-metric purple"><span>Avg Daily</span><strong>128</strong></div>
            </div>
            <h3 class="h5 fw-bold mb-3">Daily Breakdown</h3>
            <div class="table-responsive">
                <table class="table preview-table">
                    <thead><tr><th>Date</th><th>Present</th><th>Absent</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php for ($day = 1; $day <= 5; $day++): ?>
                            <tr><td>2026-04-0<?php echo $day; ?></td><td>125</td><td>17</td><td>142</td></tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>