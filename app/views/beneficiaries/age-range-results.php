<?php
/**
 * Module: Beneficiary Age Range Results Partial View
 * Purpose: Renders beneficiaries filtered by age range
 */
if (!isset($beneficiaries)) {
    $beneficiaries = [];
}
$minAge = $_GET['min_age'] ?? 0;
$maxAge = $_GET['max_age'] ?? 120;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Beneficiaries by Age Range</h1>
        <a href="BeneficiaryController.php?action=list" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="BeneficiaryController.php" class="row g-3">
                <input type="hidden" name="action" value="by-age-range">
                <div class="col-md-4">
                    <label class="form-label">Min Age</label>
                    <input type="number" name="min_age" class="form-control" value="<?php echo (int)$minAge; ?>" min="0" max="120" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Age</label>
                    <input type="number" name="max_age" class="form-control" value="<?php echo (int)$maxAge; ?>" min="0" max="120" required>
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

    <div class="card">
        <div class="card-header">
            <strong>Results: Ages <?php echo (int)$minAge; ?> to <?php echo (int)$maxAge; ?></strong>
            <span class="badge bg-info ms-2"><?php echo count($beneficiaries); ?> found</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($beneficiaries)): ?>
                <div class="p-4 text-muted">No beneficiaries found in this age range.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Status</th>
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
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</write_to_file>