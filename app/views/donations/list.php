<?php
$pageTitle = 'Donations';
$cashTotal = $summary['total_cash'] ?? 15800;
$foodCount = $summary['food_donations'] ?? 8;
$totalDonations = $summary['total_donations'] ?? count($donations ?? []);
require_once __DIR__ . "/../includes/layout-header.php";
?>
<style>
    .summary-grid {
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(3, 1fr);
        margin-bottom: 24px;
    }
    .summary-tile, .table-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
    }
    .summary-tile {
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 120px;
        padding: 24px;
    }
    .summary-icon {
        border-radius: 10px;
        display: inline-flex;
        font-size: 26px;
        height: 56px;
        width: 56px;
        justify-content: center;
        align-items: center;
    }
    .summary-icon.green { background: #dcfce7; color: #009a35; }
    .summary-icon.blue { background: #dbeafe; color: #0061ff; }
    .summary-icon.purple { background: #f3e8ff; color: #a100ff; }
    .summary-label { color: #1f2a44; font-size: 16px; margin-bottom: 2px; }
    .summary-value { color: #071326; font-size: 26px; line-height: 1.1; }
    .table-card { overflow: hidden; }
    .table-card h2 { font-size: 22px; font-weight: 700; margin: 0; padding: 22px 20px; }
    .prototype-table { margin: 0; }
    .prototype-table thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        padding: 14px 20px;
    }
    .prototype-table tbody td {
        border-color: #e5e7eb;
        color: #1f2a44;
        font-size: 15px;
        padding: 14px 20px;
    }
    .type-pill {
        border-radius: 999px;
        display: inline-flex;
        font-size: 12px;
        padding: 4px 10px;
    }
    .type-pill.cash { background: #dcfce7; color: #008f35; }
    .type-pill.food { background: #dbeafe; color: #005cff; }
    .type-pill.other, .type-pill.supplies { background: #f3e8ff; color: #9b00ff; }
    @media (max-width: 768px) { .summary-grid { grid-template-columns: 1fr; } }
</style>

<section class="summary-grid">
    <div class="summary-tile">
        <span class="summary-icon green"><i class="fas fa-dollar-sign" aria-hidden="true"></i></span>
        <div>
            <div class="summary-label">Total Cash Donations (ZAR)</div>
            <div class="summary-value">R<?php echo number_format((float)$cashTotal, 0); ?></div>
        </div>
    </div>
    <div class="summary-tile">
        <span class="summary-icon blue"><i class="fas fa-cube" aria-hidden="true"></i></span>
        <div>
            <div class="summary-label">Food Donations</div>
            <div class="summary-value"><?php echo (int)$foodCount; ?> deliveries</div>
        </div>
    </div>
    <div class="summary-tile">
        <span class="summary-icon purple"><i class="fas fa-gift" aria-hidden="true"></i></span>
        <div>
            <div class="summary-label">Total Donations</div>
            <div class="summary-value"><?php echo (int)$totalDonations; ?></div>
        </div>
    </div>
</section>

<section class="table-card">
    <h2>Donation History</h2>
    <div class="table-responsive">
        <table class="table prototype-table">
            <thead>
                <tr><th>ID</th><th>DONOR NAME</th><th>TYPE</th><th>DESCRIPTION</th><th>QUANTITY/AMOUNT</th><th>DATE</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($donations)): ?>
                    <?php foreach ($donations as $donation): ?>
                        <?php
                        $type = strtolower($donation['DonationType'] ?? 'other');
                        $amount = (float)($donation['Amount'] ?? 0);
                        ?>
                        <tr>
                            <td><?php echo 'DON-' . str_pad((string)(int)$donation['DonationID'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($donation['DonorName']); ?></td>
                            <td><span class="type-pill <?php echo htmlspecialchars($type); ?>"><?php echo ucfirst($type); ?></span></td>
                            <td><?php echo htmlspecialchars($donation['Description'] ?? 'Donation'); ?></td>
                            <td><?php echo $amount > 0 ? 'R' . number_format($amount, 0) : 'Mixed'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($donation['DonationDate'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ([
                        ['DON-001', 'ABC Corporation', 'cash', 'Monthly contribution', 'R5,000', '2026-04-28'],
                        ['DON-002', 'John Smith', 'food', 'Rice - 50kg', '50kg', '2026-04-27'],
                        ['DON-003', 'Community Church', 'cash', 'Easter donation', 'R3,500', '2026-04-25'],
                        ['DON-004', 'Local Supermarket', 'food', 'Assorted goods', 'Mixed', '2026-04-24'],
                        ['DON-005', 'Anonymous', 'cash', 'General donation', 'R1,200', '2026-04-22'],
                        ['DON-006', 'XYZ Foundation', 'other', 'Kitchen equipment', 'N/A', '2026-04-20'],
                    ] as $row): ?>
                        <tr>
                            <td><?php echo $row[0]; ?></td>
                            <td><?php echo $row[1]; ?></td>
                            <td><span class="type-pill <?php echo $row[2]; ?>"><?php echo ucfirst($row[2]); ?></span></td>
                            <td><?php echo $row[3]; ?></td>
                            <td><?php echo $row[4]; ?></td>
                            <td><?php echo $row[5]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>