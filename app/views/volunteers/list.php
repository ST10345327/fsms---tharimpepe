<?php
$pageTitle = 'Volunteers';
$pageSubtitle = 'Tharimpepe Feeding Scheme';
require_once __DIR__ . "/../includes/layout-header.php";
?>

<nav class="fsms-module-tabs" aria-label="Volunteer views">
    <a class="fsms-module-tab" href="VolunteerScheduleController.php?action=list">Schedule View</a>
    <a class="fsms-module-tab active" href="VolunteerController.php?action=list" aria-current="page">Volunteers List</a>
</nav>

<div class="fsms-section-head">
    <h2>Volunteer Directory</h2>
    <a class="fsms-btn fsms-btn-primary" href="VolunteerController.php?action=create">
        <i class="fas fa-plus"></i> Add Volunteer
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="fsms-alert fsms-alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="fsms-alert fsms-alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="fsms-grid-4" style="margin-bottom:20px;">
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Available</div>
            <div class="fsms-stat-value"><?php echo $statusCounts['available'] ?? 0; ?></div>
        </div>
        <span class="fsms-stat-icon green"><i class="fas fa-check"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Unavailable</div>
            <div class="fsms-stat-value"><?php echo $statusCounts['unavailable'] ?? 0; ?></div>
        </div>
        <span class="fsms-stat-icon orange"><i class="fas fa-ban"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">On Leave</div>
            <div class="fsms-stat-value"><?php echo $statusCounts['on_leave'] ?? 0; ?></div>
        </div>
        <span class="fsms-stat-icon purple"><i class="fas fa-plane"></i></span>
    </div>
    <div class="fsms-stat">
        <div>
            <div class="fsms-stat-label">Total</div>
            <div class="fsms-stat-value"><?php echo count($volunteers ?? []); ?></div>
        </div>
        <span class="fsms-stat-icon blue"><i class="fas fa-users"></i></span>
    </div>
</div>

<div class="fsms-tabs">
    <a class="fsms-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" href="VolunteerController.php?action=list">All</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'available' ? 'active' : ''; ?>" href="VolunteerController.php?action=list&status=available">Available</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'unavailable' ? 'active' : ''; ?>" href="VolunteerController.php?action=list&status=unavailable">Unavailable</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'on_leave' ? 'active' : ''; ?>" href="VolunteerController.php?action=list&status=on_leave">On Leave</a>
</div>

<div class="fsms-card">
    <div class="fsms-table-wrap">
        <table class="fsms-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($volunteers)): ?>
                    <?php foreach ($volunteers as $v): ?>
                        <?php
                        $status = strtolower($v['AvailabilityStatus'] ?? 'available');
                        $badgeClass = $status === 'available' ? 'fsms-badge-green' : ($status === 'on_leave' ? 'fsms-badge-gray' : 'fsms-badge-red');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim($v['FirstName'] . ' ' . $v['LastName'])); ?></td>
                            <td><?php echo htmlspecialchars($v['Email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($v['Phone'] ?? ''); ?></td>
                            <td><span class="fsms-badge <?php echo $badgeClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="VolunteerController.php?action=view&id=<?php echo (int)$v['VolunteerID']; ?>" class="text-primary" aria-label="View"><i class="fas fa-eye"></i></a>
                                    <a href="VolunteerController.php?action=edit&id=<?php echo (int)$v['VolunteerID']; ?>" class="text-primary" aria-label="Edit"><i class="fas fa-edit"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="fsms-alert fsms-alert-info" style="margin:0;">
                                <i class="fas fa-info-circle"></i> No volunteers found.
                                <a href="VolunteerController.php?action=create">Register a volunteer</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
