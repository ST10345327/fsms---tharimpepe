<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - FSMS</title>
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
        .page-header h1 { margin: 0; font-weight: 700; }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table thead { background: #f8f9fa; }
        .role-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .role-admin { background: #dc3545; color: white; }
        .role-volunteer { background: #28a745; color: white; }
        .role-staff { background: #17a2b8; color: white; }
        .role-donor { background: #fd7e14; color: white; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .btn-success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #5568d3 0%, #693a90 100%); }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-users"></i> User Management</h1>
                    <p class="mb-0 mt-2">System user administration and access control</p>
                </div>
                <a href="UserController.php?action=create" class="btn btn-light">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-card">
            <h5 class="mb-3">Filter Users</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="list">
                
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or email" 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="volunteer" <?php echo ($_GET['role'] ?? '') === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                        <option value="staff" <?php echo ($_GET['role'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="donor" <?php echo ($_GET['role'] ?? '') === 'donor' ? 'selected' : ''; ?>>Donor</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['Username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo strtolower($user['Role']); ?>">
                                            <?php echo ucfirst($user['Role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?php echo strtolower($user['Status']); ?>">
                                            <?php echo ucfirst($user['Status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></td>
                                    <td>
                                        <a href="UserController.php?action=view&id=<?php echo (int)$user['UserID']; ?>" 
                                           class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="UserController.php?action=edit&id=<?php echo (int)$user['UserID']; ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="UserController.php?action=delete&id=<?php echo (int)$user['UserID']; ?>" 
                                           class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No users found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=list&page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?action=list&page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=list&page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <a href="UserController.php?action=role_management" class="btn btn-outline-primary btn-lg w-100">
                    <i class="fas fa-shield-alt"></i> Manage Roles
                </a>
            </div>
            <div class="col-md-6">
                <a href="UserController.php?action=activity_log" class="btn btn-outline-info btn-lg w-100">
                    <i class="fas fa-history"></i> Activity Log
                </a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
