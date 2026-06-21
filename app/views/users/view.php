<?php
$pageTitle = 'User Details';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user-circle"></i> User Details</h1>
                    <p class="mb-0 mt-2"><?php echo htmlspecialchars($user['FullName']); ?></p>
                </div>
                <a href="UserController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="content-card">
                    <h3><i class="fas fa-user"></i> User Information</h3>
                    <div class="detail-section">
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Username</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Username']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Full Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['FullName']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">
                                    <a href="mailto:<?php echo htmlspecialchars($user['Email']); ?>">
                                        <?php echo htmlspecialchars($user['Email']); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Phone'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Status -->
                    <h3 class="mt-4"><i class="fas fa-toggle-on"></i> Account Status</h3>
                    <div class="detail-section">
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Role</div>
                                <div class="detail-value">
                                    <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                                        <?php echo ucfirst($user['Role']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="badge status-<?php echo strtolower($user['Status']); ?>" style="padding: 0.5rem 1rem; font-size: 1rem;">
                                        <?php echo ucfirst($user['Status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value"><?php echo isset($user['UpdatedAt']) ? date('M d, Y', strtotime($user['UpdatedAt'])) : 'Never'; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="UserController.php?action=edit&id=<?php echo (int)$user['UserID']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <a href="UserController.php?action=delete&id=<?php echo (int)$user['UserID']; ?>" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete User
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-action"><?php echo ucwords(str_replace('_', ' ', $activity['Action'])); ?></div>
                                <div class="text-muted small mt-1"><?php echo htmlspecialchars($activity['Details'] ?? ''); ?></div>
                                <div class="activity-time">
                                    <i class="fas fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($activity['Timestamp'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No recent activity
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Account Summary -->
                <div class="content-card text-center">
                    <i class="fas fa-user-circle fa-5x mb-3" style="color: #667eea;"></i>
                    <h5><?php echo htmlspecialchars($user['FullName']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['Username']); ?></p>
                    <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                        <?php echo ucfirst($user['Role']); ?>
                    </span>
                    <p class="mt-3 mb-0">
                        <span class="badge status-<?php echo strtolower($user['Status']); ?>" style="padding: 0.5rem 1rem;">
                            <?php echo ucfirst($user['Status']); ?>
                        </span>
                    </p>
                </div>

                <!-- Quick Actions -->
                <div class="content-card">
                    <h6 class="mb-3"><i class="fas fa-cogs"></i> Quick Actions</h6>
                    <a href="UserController.php?action=edit&id=<?php echo (int)$user['UserID']; ?>" class="btn btn-outline-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Information
                    </a>
                    <a href="UserController.php?action=role_management" class="btn btn-outline-primary w-100">
                        <i class="fas fa-shield-alt"></i> Manage Role
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
