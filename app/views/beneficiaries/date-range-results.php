<?php
/**
 * Module: Beneficiary Date Range Results Partial View
 * Purpose: Renders beneficiaries filtered by registration date range
 */
if (!isset($beneficiaries)) {
    $beneficiaries = [];
}
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Beneficiaries by Date Range</h1>
        <a href="BeneficiaryController.php?action=list" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="BeneficiaryController.php" class="row g-3">
                <input type="hidden" name="action" value="by-date-range">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($error) && !empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($startDate) && !empty($endDate)): ?>
        <div class="card">
            <div class="card-header">
                <strong>Results: <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?></strong>
                <span class="badge bg-info ms-2"><?php echo count($beneficiaries); ?> found</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($beneficiaries)): ?>
                    <div class="p-4 text-muted">No beneficiaries found in this date range.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($beneficiaries as $b): ?>
                                <tr>
                                    <td><?php echo (int)($b['BeneficiaryID'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars(($b['FirstName'] ?? '') . ' ' . ($b['LastName'] ?? '')); ?></td>
                                    <td><?php echo (int)($b['Age'] ?? 0); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($b['Status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($b['Status'] ?? 'unknown'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($b['RegistrationDate'] ?? ''); ?></td>
                                    <td>
                                        <a href="BeneficiaryController.php?action=view&id=<?php echo (int)($b['BeneficiaryID'] ?? 0); ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</write_to_file>