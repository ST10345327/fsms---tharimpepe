<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .donation-item {
            border-left: 4px solid #28a745;
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .donation-item:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .donation-item.cash { border-left-color: #28a745; }
        .donation-item.food { border-left-color: #fd7e14; }
        .donation-item.supplies { border-left-color: #17a2b8; }
        .donation-item.other { border-left-color: #6c757d; }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .donor-name { font-size: 1.2rem; font-weight: 600; color: #333; }
        .type-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .type-badge.cash { background: #d4edda; color: #155724; }
        .type-badge.food { background: #fff3cd; color: #856404; }
        .type-badge.supplies { background: #d1ecf1; color: #0c5460; }
        .type-badge.other { background: #e2e3e5; color: #383d41; }
        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-item { padding: 10px; background: #f8f9fa; border-radius: 6px; }
        .detail-label { font-size: 0.9rem; color: #666; font-weight: 500; }
        .detail-value { font-size: 1.1rem; color: #333; font-weight: 600; margin-top: 5px; }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-sm { padding: 6px 12px; font-size: 0.9rem; }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-success:hover { background: linear-gradient(135deg, #218838 0%, #1aa085 100%); }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .search-box { margin-bottom: 20px; }
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #ddd;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .filter-btn:hover, .filter-btn.active {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-gift"></i> <?php echo htmlspecialchars($pageTitle); ?></h1>
                    <p class="mb-0 mt-2">Track and manage donations from donors</p>
                </div>
                <a href="DonationController.php?action=create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus"></i> Record Donation
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid pt-4 pb-5">
        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <?php if ($summary): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo (int)$summary['total_donations']; ?></div>
                    <div class="stat-label">Total Donations</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">R<?php echo number_format($summary['total_cash'] ?? 0, 2); ?></div>
                    <div class="stat-label">Cash Donations</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo (int)$summary['unique_donors']; ?></div>
                    <div class="stat-label">Total Donors</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number"><?php echo (int)($summary['food_donations'] + $summary['supplies_donations']); ?></div>
                    <div class="stat-label">In-Kind Donations</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="content-card search-box">
            <form method="GET" action="DonationController.php" class="row g-2">
                <input type="hidden" name="action" value="list">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by donor name, email, or description..." 
                           value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="DonationController.php?action=list" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Type Filter -->
        <div class="filter-tabs">
            <a href="DonationController.php?action=list" class="filter-btn <?php echo empty($typeFilter) ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All Types
            </a>
            <a href="DonationController.php?action=list&type=cash" class="filter-btn <?php echo $typeFilter === 'cash' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill"></i> Cash
            </a>
            <a href="DonationController.php?action=list&type=food" class="filter-btn <?php echo $typeFilter === 'food' ? 'active' : ''; ?>">
                <i class="fas fa-apple-alt"></i> Food
            </a>
            <a href="DonationController.php?action=list&type=supplies" class="filter-btn <?php echo $typeFilter === 'supplies' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Supplies
            </a>
            <a href="DonationController.php?action=list&type=other" class="filter-btn <?php echo $typeFilter === 'other' ? 'active' : ''; ?>">
                <i class="fas fa-ellipsis-h"></i> Other
            </a>
        </div>

        <!-- Donations List -->
        <div class="content-card">
            <h4 class="mb-4"><i class="fas fa-list"></i> Donation Records</h4>

            <?php if (!empty($donations)): ?>
                <?php foreach ($donations as $donation): ?>
                    <div class="donation-item <?php echo htmlspecialchars($donation['DonationType']); ?>">
                        <div class="item-header">
                            <div>
                                <div class="donor-name"><?php echo htmlspecialchars($donation['DonorName']); ?></div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($donation['DonationDate'])); ?>
                                </small>
                            </div>
                            <span class="type-badge <?php echo htmlspecialchars($donation['DonationType']); ?>">
                                <?php 
                                $typeLabel = match($donation['DonationType']) {
                                    'cash' => '💵 Cash',
                                    'food' => '🍎 Food',
                                    'supplies' => '📦 Supplies',
                                    'other' => '📝 Other'
                                };
                                echo $typeLabel;
                                ?>
                            </span>
                        </div>

                        <div class="item-details">
                            <div class="detail-item">
                                <div class="detail-label">Donor</div>
                                <div class="detail-value"><?php echo htmlspecialchars($donation['DonorName']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value"><?php echo ucfirst(htmlspecialchars($donation['DonationType'])); ?></div>
                            </div>
                            <?php if ((float)$donation['Amount'] > 0): ?>
                            <div class="detail-item">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value">R<?php echo number_format((float)$donation['Amount'], 2); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($donation['DonorEmail'])): ?>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value"><a href="mailto:<?php echo htmlspecialchars($donation['DonorEmail']); ?>"><?php echo htmlspecialchars($donation['DonorEmail']); ?></a></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($donation['Description'])): ?>
                            <div class="alert alert-sm alert-secondary mb-3">
                                <small><strong>Details:</strong> <?php echo htmlspecialchars($donation['Description']); ?></small>
                            </div>
                        <?php endif; ?>

                        <div class="action-buttons">
                            <a href="DonationController.php?action=view&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="DonationController.php?action=edit&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="DonationController.php?action=delete&id=<?php echo (int)$donation['DonationID']; ?>" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">No donations found. <a href="DonationController.php?action=create">Record your first donation</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                            <a class="page-link" href="DonationController.php?action=list&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="content-card text-center">
                    <i class="fas fa-chart-bar fa-2x mb-3" style="color: #28a745;"></i>
                    <h5>Donation Reports</h5>
                    <p class="text-muted">View detailed statistics and analytics</p>
                    <a href="DonationController.php?action=report" class="btn btn-success">
                        <i class="fas fa-chart-pie"></i> View Reports
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center">
                    <i class="fas fa-star fa-2x mb-3" style="color: #ffc107;"></i>
                    <h5>Top Donors</h5>
                    <p class="text-muted">Recognize our most generous supporters</p>
                    <a href="DonationController.php?action=top-donors" class="btn btn-success">
                        <i class="fas fa-trophy"></i> View Top Donors
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center">
                    <i class="fas fa-plus-circle fa-2x mb-3" style="color: #28a745;"></i>
                    <h5>Record Donation</h5>
                    <p class="text-muted">Add a new donation to the system</p>
                    <a href="DonationController.php?action=create" class="btn btn-success">
                        <i class="fas fa-plus"></i> New Donation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
