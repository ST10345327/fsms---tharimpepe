<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - FSMS</title>
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
            margin-bottom: 30px;
        }
        .table thead { background: #f8f9fa; }
        .activity-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .action-create { background: #d4edda; color: #155724; }
        .action-update { background: #d1ecf1; color: #0c5460; }
        .action-delete { background: #f8d7da; color: #721c24; }
        .action-view { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-history"></i> Activity Log</h1>
                    <p class="mb-0 mt-2">System activity audit trail</p>
                </div>
                <a href="UserController.php?action=list" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('M d, Y h:i A', strtotime($log['Timestamp'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['Username'] ?? 'System'); ?></td>
                                    <td>
                                        <span class="activity-badge action-<?php 
                                            echo strpos($log['Action'], 'create') !== false ? 'create' : 
                                                 (strpos($log['Action'], 'delete') !== false ? 'delete' : 
                                                  (strpos($log['Action'], 'update') !== false ? 'update' : 'view')); 
                                        ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $log['Action'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['AffectedEntityName']); ?> <em class="text-muted">#<?php echo (int)$log['AffectedEntityID']; ?></em></td>
                                    <td><?php echo htmlspecialchars(substr($log['Details'] ?? '', 0, 100)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No activity logs found
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
                                <a class="page-link" href="?action=activity_log&page=<?php echo $page - 1; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?action=activity_log&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=activity_log&page=<?php echo $page + 1; ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
