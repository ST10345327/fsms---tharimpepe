<?php
$pageTitle = 'Donation Receipt';
require_once __DIR__ . '/../includes/layout-header.php';
$donation = $donation ?? [];
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="fsms-card">
            <div class="fsms-card-body">
                <div style="text-align:center;margin-bottom:28px;padding-bottom:20px;border-bottom:2px dashed var(--fsms-border);">
                    <h2 style="font-weight:800;color:var(--fsms-navy);margin-bottom:4px;">THARIMPEPE FEEDING SCHEME</h2>
                    <p style="color:var(--fsms-muted);margin:0;">Donation Receipt</p>
                </div>

                <div style="display:flex;justify-content:space-between;margin-bottom:24px;">
                    <div>
                        <div style="font-size:12px;color:var(--fsms-muted);text-transform:uppercase;letter-spacing:0.5px;">Receipt #</div>
                        <div style="font-size:18px;font-weight:700;"><?php echo sprintf('RCT-%04d', (int)($donation['DonationID'] ?? 0)); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:12px;color:var(--fsms-muted);text-transform:uppercase;letter-spacing:0.5px;">Date</div>
                        <div style="font-size:16px;font-weight:600;"><?php echo !empty($donation['DonationDate']) ? date('F d, Y', strtotime($donation['DonationDate'])) : '—'; ?></div>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px;color:var(--fsms-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Donor</div>
                    <div style="font-size:16px;font-weight:600;"><?php echo htmlspecialchars($donation['DonorName'] ?? '—'); ?></div>
                    <?php if (!empty($donation['DonorEmail'])): ?>
                        <div style="font-size:14px;color:var(--fsms-muted);"><?php echo htmlspecialchars($donation['DonorEmail']); ?></div>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="font-size:12px;color:var(--fsms-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Donation Details</div>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr style="border-bottom:1px solid var(--fsms-border);">
                            <td style="padding:10px 0;font-weight:600;">Type</td>
                            <td style="padding:10px 0;text-align:right;"><?php echo htmlspecialchars(ucfirst($donation['DonationType'] ?? '—')); ?></td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--fsms-border);">
                            <td style="padding:10px 0;font-weight:600;">Amount</td>
                            <td style="padding:10px 0;text-align:right;font-size:18px;font-weight:700;color:var(--fsms-green);">
                                <?php if ((float)($donation['Amount'] ?? 0) > 0): ?>
                                    R<?php echo number_format((float)$donation['Amount'], 2); ?>
                                <?php else: ?>
                                    <em style="color:var(--fsms-muted);font-weight:400;">In-kind donation</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($donation['Description'])): ?>
                        <tr style="border-bottom:1px solid var(--fsms-border);">
                            <td style="padding:10px 0;font-weight:600;">Description</td>
                            <td style="padding:10px 0;text-align:right;"><?php echo htmlspecialchars($donation['Description']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <div style="text-align:center;padding-top:16px;border-top:2px dashed var(--fsms-border);">
                    <p style="color:var(--fsms-muted);font-size:13px;margin:0;">Thank you for your generous support!</p>
                    <p style="color:var(--fsms-muted);font-size:11px;margin-top:4px;">This is a computer-generated receipt.</p>
                </div>

                <div class="d-flex gap-3 justify-content-center mt-4">
                    <button onclick="window.print()" class="btn btn-primary px-4">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <a href="DonorController.php?action=history" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .fsms-sidebar, .fsms-topbar, .btn, nav { display:none !important; }
    body:has(.fsms-sidebar) .container-fluid { margin-left:0;width:100%; }
    .fsms-card { box-shadow:none;border:1px solid #ddd; }
</style>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
