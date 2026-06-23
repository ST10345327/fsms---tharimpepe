<?php
$pageTitle = 'Donor Dashboard';
require_once __DIR__ . '/../includes/layout-header.php';
?>

<div class="fsms-grid-4">
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Total Donations</div>
            <div class="fsms-stat-value"><?php echo (int)($summary['donation_count'] ?? 0); ?></div>
            <div class="fsms-stat-meta">recorded for your account</div>
        </div>
        <span class="fsms-stat-icon blue"><i class="fas fa-gift"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Cash Contributed</div>
            <div class="fsms-stat-value">R<?php echo number_format((float)($summary['total_cash'] ?? 0), 2); ?></div>
            <div class="fsms-stat-meta">completed cash gifts</div>
        </div>
        <span class="fsms-stat-icon green"><i class="fas fa-hand-holding-dollar"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">In-Kind Gifts</div>
            <div class="fsms-stat-value"><?php echo (int)($summary['inkind_count'] ?? 0); ?></div>
            <div class="fsms-stat-meta">food &amp; supplies</div>
        </div>
        <span class="fsms-stat-icon orange"><i class="fas fa-box-open"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Last Donation</div>
            <div class="fsms-stat-value" style="font-size:1.1rem;">
                <?php echo !empty($summary['last_donation']) ? date('M d, Y', strtotime($summary['last_donation'])) : '—'; ?>
            </div>
            <div class="fsms-stat-meta">most recent gift</div>
        </div>
        <span class="fsms-stat-icon purple"><i class="fas fa-calendar-check"></i></span>
    </div>
</div>

<div class="fsms-grid-2">
    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Quick Actions</h2>
            <div class="fsms-actions">
                <a href="../controllers/DonorController.php?action=history" class="fsms-action navy">
                    <i class="fas fa-list"></i> View Donation History
                </a>
                <a href="../controllers/ProfileController.php?action=profile" class="fsms-action green">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
                <a href="../controllers/ProfileController.php?action=change_password" class="fsms-action orange">
                    <i class="fas fa-lock"></i> Change Password
                </a>
            </div>
        </div>
    </div>

    <div class="fsms-card">
        <div class="fsms-card-body">
            <h2 class="fsms-card-title">Recent Donations</h2>
            <?php if (!empty($recentDonations['data'])): ?>
                <?php foreach ($recentDonations['data'] as $donation): ?>
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-size:13px;font-weight:600;">
                                <?php echo htmlspecialchars(ucfirst($donation['DonationType'] ?? 'donation')); ?>
                            </div>
                            <div style="font-size:11px;color:var(--muted);">
                                <?php echo !empty($donation['DonationDate']) ? date('M d, Y', strtotime($donation['DonationDate'])) : ''; ?>
                            </div>
                        </div>
                        <div style="font-size:13px;font-weight:600;">
                            <?php if (strtolower($donation['DonationType'] ?? '') === 'cash'): ?>
                                R<?php echo number_format((float)($donation['Amount'] ?? 0), 2); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($donation['Description'] ?? 'In-kind'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted mb-0">No donations linked to your account yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
