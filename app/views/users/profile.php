<?php
$pageTitle = 'My Profile';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-user-circle"></i> My Profile</h1>
            <p class="mb-0 mt-2">Your account information and settings</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="content-card">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-name"><?php echo htmlspecialchars($user['FullName']); ?></div>
                        <div class="profile-meta">@<?php echo htmlspecialchars($user['Username']); ?></div>
                        <div class="mt-3">
                            <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                                <?php echo ucfirst($user['Role']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <h5 class="mb-3"><i class="fas fa-id-card"></i> Account Information</h5>
                    <div class="detail-section">
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Username</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Username']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Email']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Full Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['FullName']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($user['Phone'] ?? 'Not provided'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Status -->
                    <h5 class="mb-3"><i class="fas fa-toggle-on"></i> Account Status</h5>
                    <div class="detail-section">
                        <div class="detail-row">
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="badge <?php echo $user['Status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>" style="padding: 0.5rem 1rem;">
                                        <?php echo ucfirst($user['Status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Role</div>
                                <div class="detail-value">
                                    <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                                        <?php echo ucfirst($user['Role']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Account Created</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Last Updated</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($user['UpdatedAt'] ?? $user['CreatedAt'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="UserController.php?action=edit&id=<?php echo getCurrentUser()['user_id']; ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <a href="UserController.php?action=change_password" class="btn btn-warning btn-lg">
                            <i class="fas fa-lock"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
