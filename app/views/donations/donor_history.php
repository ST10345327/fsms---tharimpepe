<?php
$pageTitle = $pageTitle ?? 'My Donation History';
require_once __DIR__ . '/../includes/layout-header.php';
?>

<div class="fsms-page-header">
    <div class="container-fluid">
        <h1><i class="fas fa-hand-holding-dollar"></i> My Donation History</h1>
        <p class="mb-0 mt-2">Donations linked to your donor account</p>
    </div>
</div>

<div class="container-fluid pt-4 pb-5">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="fsms-stat">
                <div>
                    <div class="fsms-stat-label">Total Gifts</div>
                    <div class="fsms-stat-value"><?php echo (int)($summary['donation_count'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="fsms-stat">
                <div>
                    <div class="fsms-stat-label">Cash Total</div>
                    <div class="fsms-stat-value">R<?php echo number_format((float)($summary['total_cash'] ?? 0), 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="fsms-stat">
                <div>
                    <div class="fsms-stat-label">Last Gift</div>
                    <div class="fsms-stat-value" style="font-size:1rem;">
                        <?php echo !empty($summary['last_donation']) ? date('M d, Y', strtotime($summary['last_donation'])) : '—'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount / Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($donations)): ?>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo !empty($donation['DonationDate']) ? date('M d, Y', strtotime($donation['DonationDate'])) : '—'; ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($donation['DonationType'] ?? '')); ?></td>
                                <td>
                                    <?php if (strtolower($donation['DonationType'] ?? '') === 'cash'): ?>
                                        R<?php echo number_format((float)($donation['Amount'] ?? 0), 2); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($donation['Description'] ?? 'In-kind donation'); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(ucfirst($donation['Status'] ?? 'completed')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No donations found for your account.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="../controllers/DonorController.php?action=dashboard" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
