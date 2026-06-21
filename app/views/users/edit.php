<?php
$pageTitle = 'Edit User';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit"></i> Edit User</h1>
            <p class="mb-0 mt-2">Update user account information</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <form method="POST" action="UserController.php" class="needs-validation">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo (int)$user['UserID']; ?>">

                        <!-- User Information Section -->
                        <div class="form-section">
                            <h5><i class="fas fa-user"></i> User Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Username']); ?>" disabled>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fullname" required 
                                       value="<?php echo htmlspecialchars($user['FullName']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required 
                                       value="<?php echo htmlspecialchars($user['Email']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['Phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Account Status Section -->
                        <div class="form-section">
                            <h5><i class="fas fa-toggle-on"></i> Account Status</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="active" <?php echo $user['Status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $user['Status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['Role']); ?>" disabled>
                                <small class="text-muted">Role is managed separately in Role Management section</small>
                            </div>
                        </div>

                        <!-- Account Metadata -->
                        <div class="form-section">
                            <h5><i class="fas fa-info-circle"></i> Account Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Created</label>
                                    <input type="text" class="form-control" value="<?php echo date('M d, Y h:i A', strtotime($user['CreatedAt'])); ?>" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Updated</label>
                                    <input type="text" class="form-control" value="<?php echo isset($user['UpdatedAt']) ? date('M d, Y h:i A', strtotime($user['UpdatedAt'])) : 'Never'; ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="UserController.php?action=view&id=<?php echo (int)$user['UserID']; ?>" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
