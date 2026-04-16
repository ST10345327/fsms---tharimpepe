<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .detail-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
        }
        .detail-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
        }
        .detail-value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 600;
            margin-top: 10px;
        }
        .role-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .role-admin { background: #dc3545; color: white; }
        .role-volunteer { background: #28a745; color: white; }
        .role-staff { background: #17a2b8; color: white; }
        .role-donor { background: #fd7e14; color: white; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-action {
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .activity-time {
            color: #999;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
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

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
