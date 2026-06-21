<?php
$pageTitle = 'Activity Log';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
