<?php
$pageTitle = 'Role Management';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-shield-alt"></i> Role Management</h1>
            <p class="mb-0 mt-2">Assign and manage user roles</p>
        </div>
    </div>

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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
