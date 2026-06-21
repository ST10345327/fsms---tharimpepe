<?php
$pageTitle = 'Attendance';
require_once __DIR__ . "/../includes/layout-header.php";
?>

<div class="fsms-page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1><i class="fas fa-clipboard-check me-2"></i>Attendance</h1>
            <p>Track distribution and recipient presence</p>
        </div>
        <div class="d-flex gap-2">
            <a href="AttendanceController.php?action=bulk-record" class="fsms-btn fsms-btn-white"><i class="fas fa-users-viewfinder"></i> Bulk Record</a>
            <a href="AttendanceController.php?action=report" class="fsms-btn fsms-btn-white"><i class="fas fa-chart-pie"></i> Reports</a>
        </div>
    </div>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="fsms-alert fsms-alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['success'])): ?>
    <div class="fsms-alert fsms-alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="fsms-card clickable" onclick="location.href='AttendanceController.php?action=create'" style="cursor:pointer;">
            <div class="fsms-card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1">Individual Record</h5>
                    <p class="text-muted mb-0 small">Mark attendance for a single beneficiary</p>
                </div>
                <div style="background:rgba(37,99,235,0.1);border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;color:#2563eb;"><i class="fas fa-user-plus fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="fsms-card clickable" onclick="location.href='AttendanceController.php?action=daily-summary'" style="cursor:pointer;">
            <div class="fsms-card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1">Daily Summary</h5>
                    <p class="text-muted mb-0 small">View today's distribution logs</p>
                </div>
                <div style="background:rgba(249,115,22,0.1);border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;color:var(--orange);"><i class="fas fa-calendar-check fs-5"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="fsms-card" style="margin-bottom:24px;">
    <div class="fsms-card-body">
        <form method="GET" action="AttendanceController.php" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="list">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Session Date</label>
                <input type="date" name="date" class="fsms-input" value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted text-uppercase">Status</label>
                <select name="status" class="fsms-select">
                    <option value="">All statuses</option>
                    <option value="present" <?php echo ($statusFilter === 'present') ? 'selected' : ''; ?>>Present</option>
                    <option value="absent" <?php echo ($statusFilter === 'absent') ? 'selected' : ''; ?>>Absent</option>
                </select>
            </div>
            <div class="col-md-4"><button type="submit" class="fsms-btn fsms-btn-primary w-100"><i class="fas fa-filter me-2"></i> Apply Filters</button></div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (!empty($attendance)): ?>
        <?php foreach ($attendance as $r): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="fsms-card" style="border-top:3px solid var(--green);">
                    <div class="fsms-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 style="font-weight:600;margin:0;"><?php echo htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']); ?></h5>
                            <span class="fsms-badge <?php echo $r['Status'] === 'present' ? 'fsms-badge-green' : 'fsms-badge-red'; ?>"><?php echo ucfirst($r['Status']); ?></span>
                        </div>
                        <div style="font-size:13px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-birthday-cake me-1" style="color:var(--green);width:16px;"></i> Age: <?php echo htmlspecialchars($r['Age'] ?? 'N/A'); ?></div>
                        <div style="font-size:13px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-clock me-1" style="color:var(--green);width:16px;"></i> <?php echo date('M d, H:i', strtotime($r['CreatedAt'])); ?></div>
                        <div style="font-size:12px;color:var(--muted);"><i class="fas fa-calendar me-1" style="color:var(--green);width:16px;"></i> <?php echo date('M d, Y', strtotime($r['SessionDate'])); ?></div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="AttendanceController.php?action=view&id=<?php echo $r['AttendanceID']; ?>" class="fsms-btn fsms-btn-primary fsms-btn-sm flex-grow-1"><i class="fas fa-eye"></i> View</a>
                            <a href="AttendanceController.php?action=edit&id=<?php echo $r['AttendanceID']; ?>" class="fsms-btn fsms-btn-white fsms-btn-sm flex-grow-1"><i class="fas fa-edit"></i> Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="fsms-alert fsms-alert-info"><i class="fas fa-info-circle"></i> No records found. <a href="AttendanceController.php?action=create">Record attendance</a></div></div>
    <?php endif; ?>
</div>

<?php if (count($attendance) >= 20): ?>
<div class="d-flex justify-content-center mt-3">
    <nav><ul class="pagination">
        <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?action=list&page=<?php echo $page - 1; ?>">Previous</a></li><?php endif; ?>
        <li class="page-item active"><span class="page-link"><?php echo $page; ?></span></li>
        <li class="page-item"><a class="page-link" href="?action=list&page=<?php echo $page + 1; ?>">Next</a></li>
    </ul></nav>
</div>
<?php endif; ?>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>