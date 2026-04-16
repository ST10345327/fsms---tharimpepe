<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - FSMS</title>
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
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section h5 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-edit"></i> Edit User</h1>
            <p class="mb-0 mt-2">Update user account information</p>
        </div>
    </div>

    <!-- Main Content -->
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

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
