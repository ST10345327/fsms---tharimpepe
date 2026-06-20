<?php
/**
 * Module: Beneficiary Search Results Partial View
 * Purpose: Renders a list of beneficiaries matching a search query
 */
if (!isset($beneficiaries)) {
    $beneficiaries = [];
}
$searchTerm = $_GET['q'] ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Search Results: <?php echo htmlspecialchars($searchTerm); ?></h1>
        <a href="BeneficiaryController.php?action=list" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <?php if (empty($beneficiaries)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No beneficiaries found matching "<?php echo htmlspecialchars($searchTerm); ?>".
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
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
                                <td>
                                    <a href="BeneficiaryController.php?action=view&id=<?php echo (int)($b['BeneficiaryID'] ?? 0); ?>">
                                        <?php echo htmlspecialchars(($b['FirstName'] ?? '') . ' ' . ($b['LastName'] ?? '')); ?>
                                    </a>
                                </td>
                                <td><?php echo (int)($b['Age'] ?? 0); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($b['Status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($b['Status'] ?? 'unknown'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($b['RegistrationDate'] ?? ''); ?></td>
                                <td>
                                    <a href="BeneficiaryController.php?action=edit&id=<?php echo (int)($b['BeneficiaryID'] ?? 0); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="BeneficiaryController.php?action=view&id=<?php echo (int)($b['BeneficiaryID'] ?? 0); ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <p class="text-muted mt-2">Found <?php echo count($beneficiaries); ?> result(s)</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</write_to_file>