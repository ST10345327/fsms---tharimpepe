<?php
$pageTitle = $pageTitle ?? 'My Donation History';
require_once __DIR__ . '/../includes/layout-header.php';
?>
<style>
    .kpi-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-bottom: 24px;
    }
    .kpi-card {
        align-items: flex-start;
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        display: flex;
        justify-content: space-between;
        min-height: 120px;
        padding: 24px;
    }
    .kpi-label {
        color: #1f2a44;
        font-size: 14px;
        margin-bottom: 6px;
    }
    .kpi-value {
        color: #071326;
        font-size: 30px;
        font-weight: 400;
        line-height: 1.15;
        margin-bottom: 4px;
    }
    .kpi-meta {
        color: #334155;
        font-size: 12px;
    }
    .kpi-icon {
        align-items: center;
        border-radius: 10px;
        color: #fff;
        display: inline-flex;
        font-size: 22px;
        height: 48px;
        justify-content: center;
        width: 48px;
        flex-shrink: 0;
    }
    .kpi-icon.blue { background: #2563ff; }
    .kpi-icon.green { background: #00c950; }
    .kpi-icon.purple { background: #ad3df5; }

    .proto-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        padding: 24px;
    }

    .table thead th {
        color: #475569;
        font-size: 12px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border-bottom: 2px solid #e5e7eb;
    }
    .table td {
        vertical-align: middle;
        padding: 14px 8px;
    }
    .badge-type {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-type.cash { background: #dcfce7; color: #15803d; }
    .badge-type.food { background: #fff3cd; color: #92400e; }
    .badge-type.supplies { background: #e0f2fe; color: #075985; }
    .badge-type.other { background: #f3e8ff; color: #7c3aed; }

    .amount-cash {
        font-weight: 700;
        color: #059669;
    }
    .amount-inkind {
        color: #475569;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-4">
    <h1 style="color:#071326;font-size:24px;font-weight:700;margin-bottom:4px;">
        <i class="fas fa-hand-holding-dollar" style="color:var(--fsms-navy);margin-right:10px;"></i>My Donation History
    </h1>
    <p style="color:#475569;margin-bottom:24px;">Donations linked to your donor account</p>

    <section class="kpi-grid" aria-label="Donation summary">
        <div class="kpi-card">
            <div>
                <div class="kpi-label">Total Gifts</div>
                <div class="kpi-value"><?php echo (int)($summary['donation_count'] ?? 0); ?></div>
                <div class="kpi-meta">all-time donations</div>
            </div>
            <span class="kpi-icon blue"><i class="fas fa-gift" aria-hidden="true"></i></span>
        </div>
        <div class="kpi-card">
            <div>
                <div class="kpi-label">Cash Total</div>
                <div class="kpi-value">R<?php echo number_format((float)($summary['total_cash'] ?? 0), 2); ?></div>
                <div class="kpi-meta">total cash contributed</div>
            </div>
            <span class="kpi-icon green"><i class="fas fa-hand-holding-dollar" aria-hidden="true"></i></span>
        </div>
        <div class="kpi-card">
            <div>
                <div class="kpi-label">Last Gift</div>
                <div class="kpi-value" style="font-size:1.2rem;">
                    <?php echo !empty($summary['last_donation']) ? date('M d, Y', strtotime($summary['last_donation'])) : '—'; ?>
                </div>
                <div class="kpi-meta">
                    <?php if (!empty($summary['last_donation'])): ?>
                        <?php
                        $daysAgo = (int)((time() - strtotime($summary['last_donation'])) / 86400);
                        echo $daysAgo === 0 ? 'Today' : ($daysAgo === 1 ? 'Yesterday' : "$daysAgo days ago");
                        ?>
                    <?php else: ?>
                        no donations yet
                    <?php endif; ?>
                </div>
            </div>
            <span class="kpi-icon purple"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
        </div>
    </section>

    <div class="proto-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount / Description</th>
                        <th>Status</th>
                        <th class="text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($donations)): ?>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo !empty($donation['DonationDate']) ? date('M d, Y', strtotime($donation['DonationDate'])) : '—'; ?></td>
                                <td>
                                    <span class="badge-type <?php echo htmlspecialchars(strtolower($donation['DonationType'] ?? 'other')); ?>">
                                        <?php echo htmlspecialchars(ucfirst($donation['DonationType'] ?? 'Other')); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (strtolower($donation['DonationType'] ?? '') === 'cash'): ?>
                                        <span class="amount-cash">R<?php echo number_format((float)($donation['Amount'] ?? 0), 2); ?></span>
                                    <?php else: ?>
                                        <span class="amount-inkind"><?php echo htmlspecialchars($donation['Description'] ?? 'In-kind donation'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2" style="font-weight:600;">
                                        <i class="fas fa-check-circle" style="font-size:11px;margin-right:4px;"></i>
                                        <?php echo htmlspecialchars(ucfirst($donation['Status'] ?? 'completed')); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="../controllers/DonorController.php?action=receipt&id=<?php echo (int)($donation['DonationID'] ?? 0); ?>" class="btn btn-sm" style="background:#1b3a5c;color:#fff;border-radius:8px;font-weight:600;">
                                        <i class="fas fa-receipt"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-gift fa-3x" style="color:#d1d5db;margin-bottom:12px;"></i>
                                <p class="text-muted mb-0">No donations found for your account.</p>
                                <a href="../controllers/DonorController.php?action=create_donation" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus-circle"></i> Make Your First Donation
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <a href="../controllers/DonorController.php?action=create_donation" class="btn" style="background:#2e7d32;color:#fff;border-radius:9px;font-weight:700;padding:10px 24px;">
            <i class="fas fa-plus-circle"></i> Make a Donation
        </a>
        <a href="../controllers/DonorController.php?action=dashboard" class="btn btn-outline-secondary" style="border-radius:9px;font-weight:700;padding:10px 24px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
