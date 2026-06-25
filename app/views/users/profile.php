<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/layout-header.php';
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
        display: inline-flex;
        font-size: 22px;
        height: 48px;
        justify-content: center;
        width: 48px;
        flex-shrink: 0;
    }
    .kpi-icon.blue { background: #2563ff; color: #fff; }
    .kpi-icon.green { background: #00c950; color: #fff; }
    .kpi-icon.purple { background: #ad3df5; color: #fff; }
    .kpi-icon.orange { background: #ff5b00; color: #fff; }

    .profile-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: #1b3a5c;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 36px;
        flex-shrink: 0;
    }
    .detail-section {
        margin-bottom: 28px;
    }
    .detail-section h5 {
        color: #071326;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .detail-item {
        background: #f8fafc;
        padding: 16px;
        border-radius: 8px;
    }
    .detail-label {
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .detail-value {
        color: #071326;
        font-size: 15px;
        font-weight: 600;
    }
    .role-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .role-badge.admin { background: #fef2f2; color: #dc2626; }
    .role-badge.staff { background: #e0f2fe; color: #0369a1; }
    .role-badge.volunteer { background: #dcfce7; color: #15803d; }
    .role-badge.donor { background: #fff7ed; color: #c2410c; }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }
</style>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success'] ?? ''); unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div style="background:#fff;border:1px solid #dfe3e8;border-radius:12px;box-shadow:0 1px 3px rgba(15,23,42,0.18);padding:32px;text-align:center;margin-bottom:24px;">
            <div class="profile-avatar" style="margin:0 auto 16px;">
                <i class="fas fa-user"></i>
            </div>
            <h3 style="color:#071326;font-weight:700;margin-bottom:4px;"><?php echo htmlspecialchars($user['FullName'] ?? $user['Username']); ?></h3>
            <p style="color:#475569;margin-bottom:12px;">@<?php echo htmlspecialchars($user['Username']); ?></p>
            <div>
                <span class="role-badge <?php echo strtolower($user['Role'] ?? ''); ?>">
                    <?php echo ucfirst($user['Role'] ?? ''); ?>
                </span>
                <span class="badge <?php echo ($user['Status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary'; ?>" style="margin-left:6px;font-weight:600;">
                    <?php echo ucfirst($user['Status'] ?? ''); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div style="background:#fff;border:1px solid #dfe3e8;border-radius:12px;box-shadow:0 1px 3px rgba(15,23,42,0.18);padding:32px;margin-bottom:24px;">
            <div class="detail-section">
                <h5><i class="fas fa-id-card" style="color:var(--fsms-navy);margin-right:8px;"></i>Account Information</h5>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Username</div>
                        <div class="detail-value"><?php echo htmlspecialchars($user['Username']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($user['Email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($user['FullName'] ?? $user['Username']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($user['Phone'] ?? 'Not provided'); ?></div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h5><i class="fas fa-clock" style="color:var(--fsms-navy);margin-right:8px;"></i>Account Timeline</h5>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Created</div>
                        <div class="detail-value"><?php echo !empty($user['CreatedAt']) ? date('M d, Y', strtotime($user['CreatedAt'])) : '—'; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Updated</div>
                        <div class="detail-value"><?php echo !empty($user['UpdatedAt']) ? date('M d, Y', strtotime($user['UpdatedAt'])) : '—'; ?></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="../controllers/ProfileController.php?action=change_password" class="btn" style="background:#1b3a5c;color:#fff;border-radius:9px;font-weight:700;padding:10px 24px;">
                    <i class="fas fa-lock"></i> Change Password
                </a>
                <a href="../controllers/DashboardController.php?action=overview" class="btn btn-outline-secondary" style="border-radius:9px;font-weight:700;padding:10px 24px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
