<?php
$pageTitle = 'Beneficiaries';
require_once __DIR__ . "/../includes/layout-header.php";
?>

<div class="fsms-page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1><i class="fas fa-users me-2"></i>Beneficiaries</h1>
            <p>Manage community members and recipients</p>
        </div>
        <a href="BeneficiaryController.php?action=create" class="fsms-btn fsms-btn-white"><i class="fas fa-plus"></i> New Beneficiary</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="fsms-alert fsms-alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="fsms-alert fsms-alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="fsms-grid-4" style="margin-bottom:20px;">
    <div class="fsms-card"><div class="fsms-card-body text-center"><div style="font-size:28px;font-weight:700;color:var(--navy);"><?php echo $totalCount ?? 0; ?></div><div class="fsms-stat-label">Total</div></div></div>
    <div class="fsms-card"><div class="fsms-card-body text-center"><div style="font-size:28px;font-weight:700;color:var(--green);"><?php echo $statusCounts['active'] ?? 0; ?></div><div class="fsms-stat-label">Active</div></div></div>
    <div class="fsms-card"><div class="fsms-card-body text-center"><div style="font-size:28px;font-weight:700;color:var(--muted);"><?php echo $statusCounts['inactive'] ?? 0; ?></div><div class="fsms-stat-label">Inactive</div></div></div>
    <div class="fsms-card"><div class="fsms-card-body text-center"><div style="font-size:28px;font-weight:700;color:var(--red);"><?php echo $statusCounts['suspended'] ?? 0; ?></div><div class="fsms-stat-label">Suspended</div></div></div>
</div>

<div class="fsms-card" style="margin-bottom:20px;">
    <div class="fsms-card-body">
        <form method="GET" action="BeneficiaryController.php" class="row g-2">
            <input type="hidden" name="action" value="search">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-0 bg-light" placeholder="Search by name, ID..." required minlength="2" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-auto"><button type="submit" class="fsms-btn fsms-btn-primary">Search</button></div>
        </form>
        <div class="d-flex gap-2 mt-3">
            <button class="fsms-btn fsms-btn-white btn-sm" data-bs-toggle="collapse" data-bs-target="#ageFilter"><i class="fas fa-child"></i> Age Range</button>
            <button class="fsms-btn fsms-btn-white btn-sm" data-bs-toggle="collapse" data-bs-target="#dateFilter"><i class="fas fa-calendar"></i> Date Range</button>
        </div>
        <div class="collapse mt-3" id="ageFilter">
            <div class="card card-body p-3" style="background:#f9fafb;">
                <form method="GET" action="BeneficiaryController.php" class="row g-2 align-items-end">
                    <input type="hidden" name="action" value="by-age-range">
                    <div class="col-5"><input type="number" name="min_age" class="fsms-input" placeholder="Min Age"></div>
                    <div class="col-5"><input type="number" name="max_age" class="fsms-input" placeholder="Max Age"></div>
                    <div class="col-2"><button type="submit" class="fsms-btn fsms-btn-primary w-100"><i class="fas fa-check"></i></button></div>
                </form>
            </div>
        </div>
        <div class="collapse mt-3" id="dateFilter">
            <div class="card card-body p-3" style="background:#f9fafb;">
                <form method="GET" action="BeneficiaryController.php" class="row g-2">
                    <input type="hidden" name="action" value="by-date-range">
                    <div class="col-5"><input type="date" name="start_date" class="fsms-input" required></div>
                    <div class="col-5"><input type="date" name="end_date" class="fsms-input" required></div>
                    <div class="col-2"><button type="submit" class="fsms-btn fsms-btn-primary w-100"><i class="fas fa-check"></i></button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="fsms-tabs">
    <a class="fsms-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" href="BeneficiaryController.php?action=list">All</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'active' ? 'active' : ''; ?>" href="BeneficiaryController.php?action=list&status=active">Active</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'active' : ''; ?>" href="BeneficiaryController.php?action=list&status=inactive">Inactive</a>
    <a class="fsms-tab <?php echo ($_GET['status'] ?? '') === 'suspended' ? 'active' : ''; ?>" href="BeneficiaryController.php?action=list&status=suspended">Suspended</a>
</div>

<div class="row">
    <?php if (!empty($beneficiaries)): ?>
        <?php foreach ($beneficiaries as $b): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="fsms-card" style="border-top:3px solid var(--green);">
                    <div class="fsms-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 style="font-weight:600;margin:0;"><?php echo htmlspecialchars($b['FirstName'] . ' ' . $b['LastName']); ?></h5>
                            <span class="fsms-badge fsms-badge-green"><?php echo ucfirst($b['Status']); ?></span>
                        </div>
                        <div style="font-size:13px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-birthday-cake me-1" style="color:var(--green);width:16px;"></i> Age: <?php echo htmlspecialchars($b['Age'] ?? 'N/A'); ?></div>
                        <div style="font-size:13px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-calendar-alt me-1" style="color:var(--green);width:16px;"></i> <?php echo date('d M Y', strtotime($b['RegistrationDate'])); ?></div>
                        <?php if (!empty($b['Notes'])): ?>
                            <div style="font-size:13px;color:var(--muted);"><i class="fas fa-sticky-note me-1" style="color:var(--green);width:16px;"></i> <?php echo htmlspecialchars(substr($b['Notes'], 0, 50)); ?><?php if (strlen($b['Notes']) > 50): ?>...<?php endif; ?></div>
                        <?php endif; ?>
                        <div class="d-flex gap-2 mt-3">
                            <a href="BeneficiaryController.php?action=view&id=<?php echo $b['BeneficiaryID']; ?>" class="fsms-btn fsms-btn-primary fsms-btn-sm flex-grow-1"><i class="fas fa-eye"></i> View</a>
                            <a href="BeneficiaryController.php?action=edit&id=<?php echo $b['BeneficiaryID']; ?>" class="fsms-btn fsms-btn-white fsms-btn-sm flex-grow-1"><i class="fas fa-edit"></i> Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="fsms-alert fsms-alert-info"><i class="fas fa-info-circle"></i> No beneficiaries found. <a href="BeneficiaryController.php?action=create">Register a beneficiary</a></div></div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>