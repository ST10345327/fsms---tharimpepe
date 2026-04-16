<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Report - FSMS</title>
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
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .filter-form {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-people-arrows"></i> Beneficiary Report</h1>
            <p class="mb-0 mt-2">Registered beneficiaries and their program participation</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Filter Form -->
        <div class="filter-form">
            <h6 class="mb-3">Filter Beneficiaries</h6>
            <form method="GET" class="row g-3">
                <input type="hidden" name="action" value="beneficiaries">
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role_filter">
                        <option value="">All Roles</option>
                        <option value="Student" <?php echo ($_GET['role_filter'] ?? '') === 'Student' ? 'selected' : ''; ?>>Student</option>
                        <option value="Elderly" <?php echo ($_GET['role_filter'] ?? '') === 'Elderly' ? 'selected' : ''; ?>>Elderly</option>
                        <option value="Disabled" <?php echo ($_GET['role_filter'] ?? '') === 'Disabled' ? 'selected' : ''; ?>>Disabled</option>
                        <option value="Family" <?php echo ($_GET['role_filter'] ?? '') === 'Family' ? 'selected' : ''; ?>>Family</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status_filter">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo ($_GET['status_filter'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($_GET['status_filter'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Suspended" <?php echo ($_GET['status_filter'] ?? '') === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Name or ID">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="ReportsController.php?action=beneficiaries" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Beneficiary Table -->
        <div class="table-card">
            <h5 class="mb-3">
                Beneficiary List
                <small class="text-muted">(<?php echo count($beneficiaryData ?? []); ?> beneficiaries)</small>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Beneficiary ID</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($beneficiaryData)): ?>
                            <?php foreach ($beneficiaryData as $beneficiary): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($beneficiary['BeneficiaryID'] ?? ''); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($beneficiary['FullName'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($beneficiary['ContactNumber'] ?? '-'); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($beneficiary['Role'] ?? ''); ?></span></td>
                                    <td>
                                        <?php 
                                            $status = $beneficiary['Status'] ?? 'Unknown';
                                            $statusBg = $status === 'Active' ? 'success' : ($status === 'Inactive' ? 'secondary' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $statusBg; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($beneficiary['RegistrationDate'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($beneficiary['Address'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No beneficiary records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export & Back -->
        <div class="d-flex gap-2 justify-content-between">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="ReportsController.php?action=dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
