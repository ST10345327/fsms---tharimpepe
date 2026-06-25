<?php
$pageTitle = 'My Impact Report';
require_once __DIR__ . '/../includes/layout-header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="fsms-card">
            <div class="fsms-card-body">
                <h2 class="fsms-card-title"><i class="fas fa-chart-line"></i> Your Impact Overview</h2>
                <p class="text-muted">A summary of the difference your contributions have made.</p>
            </div>
        </div>
    </div>
</div>

<div class="fsms-grid-4 mt-4">
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Total Donations</div>
            <div class="fsms-stat-value"><?php echo (int)($summary['donation_count'] ?? 0); ?></div>
            <div class="fsms-stat-meta">gifts recorded</div>
        </div>
        <span class="fsms-stat-icon blue"><i class="fas fa-gift"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Cash Contributed</div>
            <div class="fsms-stat-value">R<?php echo number_format((float)($summary['total_cash'] ?? 0), 2); ?></div>
            <div class="fsms-stat-meta">total cash gifts</div>
        </div>
        <span class="fsms-stat-icon green"><i class="fas fa-hand-holding-dollar"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">In-Kind Items</div>
            <div class="fsms-stat-value"><?php echo (int)($summary['inkind_count'] ?? 0); ?></div>
            <div class="fsms-stat-meta">food &amp; supplies</div>
        </div>
        <span class="fsms-stat-icon orange"><i class="fas fa-box-open"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">First Donation</div>
            <div class="fsms-stat-value" style="font-size:1.1rem;">
                <?php echo !empty($summary['first_donation']) ? date('M d, Y', strtotime($summary['first_donation'])) : '—'; ?>
            </div>
            <div class="fsms-stat-meta">when you started giving</div>
        </div>
        <span class="fsms-stat-icon purple"><i class="fas fa-calendar-check"></i></span>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="fsms-card">
            <div class="fsms-card-body">
                <h3 class="fsms-card-title"><i class="fas fa-heart" style="color:var(--fsms-red);"></i> Your Generosity</h3>
                <p>Every donation, no matter the size, helps us provide nutritious meals to those in need. Your contributions directly support our feeding programmes and community outreach initiatives.</p>
                <?php if ((int)($summary['donation_count'] ?? 0) > 0): ?>
                    <div style="background:var(--fsms-bg);border-radius:8px;padding:16px;margin-top:12px;">
                        <div style="font-size:13px;color:var(--fsms-muted);">Average per gift</div>
                        <div style="font-size:22px;font-weight:800;color:var(--fsms-green);">
                            R<?php echo number_format((float)($summary['total_cash'] ?? 0) / max(1, (int)($summary['donation_count'] ?? 1)), 2); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="fsms-card">
            <div class="fsms-card-body">
                <h3 class="fsms-card-title"><i class="fas fa-clock" style="color:var(--fsms-teal);"></i> Donation Timeline</h3>
                <?php if (!empty($donations) && is_array($donations)): ?>
                    <div style="max-height:240px;overflow-y:auto;">
                        <?php foreach ($donations as $d): ?>
                            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--fsms-border);">
                                <div>
                                    <div style="font-size:13px;font-weight:600;"><?php echo htmlspecialchars(ucfirst($d['DonationType'] ?? 'donation')); ?></div>
                                    <div style="font-size:11px;color:var(--fsms-muted);"><?php echo !empty($d['DonationDate']) ? date('M d, Y', strtotime($d['DonationDate'])) : ''; ?></div>
                                </div>
                                <div style="font-size:13px;font-weight:600;">
                                    <?php if (strtolower($d['DonationType'] ?? '') === 'cash'): ?>
                                        R<?php echo number_format((float)($d['Amount'] ?? 0), 2); ?>
                                    <?php else: ?>
                                        <span style="color:var(--fsms-muted);">In-kind</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No donations yet. Start making an impact today!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3 mb-4">
    <a href="DonorController.php?action=dashboard" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
