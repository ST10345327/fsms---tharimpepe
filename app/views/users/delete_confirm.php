<?php
$pageTitle = 'Delete User';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete User</h1>
            <p class="mb-0 mt-2">Warning: User will be deactivated</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="content-card">
                    <div class="warning-box">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h5 class="mb-2">Are you sure?</h5>
                                <p class="mb-0">This user account will be deactivated. The account can be reactivated later if needed.</p>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">User to be deactivated:</h5>
                    <div class="user-info">
                        <div>
                            <div class="info-item">
                                <div class="text-muted small">Username</div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['Username']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="text-muted small">Full Name</div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['FullName']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="text-muted small">Email</div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['Email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="text-muted small">Role</div>
                                <div class="fw-bold"><?php echo ucfirst($user['Role']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        <strong>IMPORTANT:</strong> This action will deactivate the user's account, preventing them from logging in. All their historical data will remain in the system.
                    </div>

                    <div class="action-buttons">
                        <a href="UserController.php?action=list" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <form method="POST" action="UserController.php">
                            <input type="hidden" name="action" value="destroy">
                            <input type="hidden" name="id" value="<?php echo (int)$user['UserID']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Deactivate User
                            </button>
                        </form>
                    </div>

                    <hr class="my-4">
                    <div class="alert alert-info" role="alert">
                        <strong><i class="fas fa-info-circle"></i> Tip:</strong> Users can be reactivated by an administrator in the User Management section.
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
