<?php
$pageTitle = 'Donor Dashboard';
require_once __DIR__ . '/../includes/layout-header.php';
$isFirstDonor = (int)($summary['donation_count'] ?? 0) === 0;
?>
<style>
    .kpi-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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
        min-height: 130px;
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
    .kpi-icon.orange { background: #ff5b00; }
    .kpi-icon.purple { background: #ad3df5; }

    .dashboard-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: 1.2fr 0.8fr;
        margin-bottom: 24px;
    }
    .proto-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        padding: 24px;
    }
    .proto-card h2 {
        color: #071326;
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .quick-actions {
        display: grid;
        gap: 13px;
    }
    .quick-action {
        align-items: center;
        border-radius: 9px;
        color: #fff;
        display: flex;
        font-weight: 700;
        gap: 14px;
        min-height: 48px;
        padding: 12px 18px;
        text-decoration: none;
    }
    .quick-action:hover {
        color: #fff;
        filter: brightness(0.96);
    }
    .quick-action.navy { background: #1b3a5c; }
    .quick-action.green { background: #2e7d32; }
    .quick-action.orange { background: #ff5b00; }
    .quick-action.purple { background: #ad3df5; }
    .quick-action.teal { background: #0d9488; }

    .donation-row {
        align-items: flex-start;
        border-bottom: 1px solid #edf0f3;
        display: grid;
        gap: 12px;
        grid-template-columns: 10px 1fr auto;
        padding: 12px 0;
    }
    .donation-row:last-child {
        border-bottom: 0;
    }
    .donation-dot {
        background: #2e7d32;
        border-radius: 999px;
        height: 8px;
        margin-top: 7px;
        width: 8px;
    }
    .donation-type {
        color: #071326;
        font-size: 14px;
        font-weight: 600;
    }
    .donation-date {
        color: #475569;
        font-size: 12px;
    }
    .donation-amount {
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
    }
    .donation-amount.cash { color: #059669; }
    .donation-amount.inkind { color: #d97706; }

    @media (max-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="kpi-grid" aria-label="Donation key performance indicators">
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Donations</div>
            <div class="kpi-value"><?php echo (int)($summary['donation_count'] ?? 0); ?></div>
            <div class="kpi-meta">recorded for your account</div>
        </div>
        <span class="kpi-icon blue"><i class="fas fa-gift" aria-hidden="true"></i></span>
    </div>
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Cash Contributed</div>
            <div class="kpi-value">R<?php echo number_format((float)($summary['total_cash'] ?? 0), 2); ?></div>
            <div class="kpi-meta">total cash gifts</div>
        </div>
        <span class="kpi-icon green"><i class="fas fa-hand-holding-dollar" aria-hidden="true"></i></span>
    </div>
    <div class="kpi-card">
        <div>
            <div class="kpi-label">In-Kind Gifts</div>
            <div class="kpi-value"><?php echo (int)($summary['inkind_count'] ?? 0); ?></div>
            <div class="kpi-meta">food &amp; supplies</div>
        </div>
        <span class="kpi-icon orange"><i class="fas fa-box-open" aria-hidden="true"></i></span>
    </div>
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Last Donation</div>
            <div class="kpi-value" style="font-size:1.2rem;">
                <?php echo !empty($summary['last_donation']) ? date('M d', strtotime($summary['last_donation'])) : '—'; ?>
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

<section class="dashboard-grid">
    <div class="proto-card">
        <h2><i class="fas fa-clock" style="color:var(--fsms-navy);margin-right:8px;"></i>Recent Donations</h2>
        <?php if (!empty($recentDonations['data'])): ?>
            <?php foreach ($recentDonations['data'] as $donation): ?>
                <div class="donation-row">
                    <span class="donation-dot" aria-hidden="true"></span>
                    <div>
                        <div class="donation-type"><?php echo htmlspecialchars(ucfirst($donation['DonationType'] ?? 'donation')); ?></div>
                        <div class="donation-date"><?php echo !empty($donation['DonationDate']) ? date('M d, Y', strtotime($donation['DonationDate'])) : ''; ?></div>
                    </div>
                    <div class="donation-amount <?php echo strtolower($donation['DonationType'] ?? '') === 'cash' ? 'cash' : 'inkind'; ?>">
                        <?php if (strtolower($donation['DonationType'] ?? '') === 'cash'): ?>
                            R<?php echo number_format((float)($donation['Amount'] ?? 0), 2); ?>
                        <?php else: ?>
                            <i class="fas fa-gift"></i>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted mb-0">No donations yet. Start by making your first contribution!</p>
        <?php endif; ?>
        <?php if (!empty($recentDonations['data'])): ?>
            <a href="../controllers/DonorController.php?action=history" class="btn btn-outline-primary btn-sm mt-3 w-100">
                View All Donations
            </a>
        <?php endif; ?>
    </div>

    <div class="proto-card">
        <h2><i class="fas fa-bolt" style="color:var(--fsms-orange);margin-right:8px;"></i>Quick Actions</h2>
        <div class="quick-actions">
            <a href="../controllers/DonorController.php?action=create_donation" class="quick-action green">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                <span>Make a Donation</span>
            </a>
            <a href="../controllers/DonorController.php?action=history" class="quick-action navy">
                <i class="fas fa-list" aria-hidden="true"></i>
                <span>Donation History</span>
            </a>
            <a href="../controllers/DonorController.php?action=impact" class="quick-action teal">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <span>My Impact Report</span>
            </a>
            <a href="../controllers/ProfileController.php?action=profile" class="quick-action orange">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                <span>My Profile</span>
            </a>
            <a href="../controllers/ProfileController.php?action=change_password" class="quick-action purple">
                <i class="fas fa-lock" aria-hidden="true"></i>
                <span>Change Password</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
