<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management - FSMS</title>
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
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .role-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .user-info { flex-grow: 1; }
        .user-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
        }
        .user-email {
            color: #666;
            font-size: 0.9rem;
        }
        .role-selector {
            min-width: 200px;
        }
        .role-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .role-admin { background: #dc3545; color: white; }
        .role-volunteer { background: #28a745; color: white; }
        .role-staff { background: #17a2b8; color: white; }
        .role-donor { background: #fd7e14; color: white; }
        .role-descriptions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .role-desc {
            margin-bottom: 15px;
        }
        .role-desc:last-child { margin-bottom: 0; }
        .role-desc h6 {
            color: #333;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-shield-alt"></i> Role Management</h1>
            <p class="mb-0 mt-2">Assign and manage user roles</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Role Descriptions -->
        <div class="role-descriptions">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Role Definitions</h5>
            <div class="role-desc">
                <h6><span class="role-badge role-admin">ADMIN</span></h6>
                <p class="mb-1">Full system access. Can manage users, system settings, and all modules.</p>
            </div>
            <div class="role-desc">
                <h6><span class="role-badge role-staff">STAFF</span></h6>
                <p class="mb-1">Data management access. Can manage beneficiaries, attendance, and food stock.</p>
            </div>
            <div class="role-desc">
                <h6><span class="role-badge role-volunteer">VOLUNTEER</span></h6>
                <p class="mb-1">Limited access. Can record attendance and view beneficiary information.</p>
            </div>
            <div class="role-desc">
                <h6><span class="role-badge role-donor">DONOR</span></h6>
                <p class="mb-0">Can view and manage their donation records.</p>
            </div>
        </div>

        <!-- Users List -->
        <h5 class="mb-3">Assign Roles to Users</h5>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="role-item">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['FullName']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['Email']); ?></div>
                    </div>
                    
                    <form method="POST" action="UserController.php" class="role-selector" style="display: flex; gap: 10px;">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" value="<?php echo (int)$user['UserID']; ?>">
                        <select class="form-select" name="role" onchange="this.form.submit()">
                            <option value="admin" <?php echo $user['Role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="staff" <?php echo $user['Role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="volunteer" <?php echo $user['Role'] === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                            <option value="donor" <?php echo $user['Role'] === 'donor' ? 'selected' : ''; ?>>Donor</option>
                        </select>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-inbox"></i> No users found
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="UserController.php?action=list" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
