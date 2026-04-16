<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FSMS</title>
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
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 50px;
        }
        .profile-name {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        .profile-meta {
            color: #666;
            font-size: 1.1rem;
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
        }
        .detail-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
        }
        .detail-label{
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
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
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
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-user-circle"></i> My Profile</h1>
            <p class="mb-0 mt-2">Your account information and settings</p>
        </div>
    </div>

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
