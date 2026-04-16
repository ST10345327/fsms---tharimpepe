<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .user-info div {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-item {
            padding: 15px;
            background: white;
            border-radius: 6px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-exclamation-triangle"></i> Delete User</h1>
            <p class="mb-0 mt-2">Warning: User will be deactivated</p>
        </div>
    </div>

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
