<?php
$pageTitle = 'Search Results';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <!-- Search Results -->
    <div class="container pt-5 pb-5">
        <h1 class="mb-4"><i class="fas fa-search"></i> Search Results</h1>

        <?php if (!empty($volunteers)): ?>
            <p class="text-muted mb-4">Found <?php echo count($volunteers); ?> volunteer(s)</p>

            <div class="row">
                <?php foreach ($volunteers as $volunteer): ?>
                    <div class="col-md-6">
                        <div class="volunteer-card">
                            <!-- HZ-VOL-UI-005: Search result card -->
                            <h5><?php echo htmlspecialchars($volunteer['FirstName'] . ' ' . $volunteer['LastName']); ?></h5>
                            
                            <div class="volunteer-info">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($volunteer['Email']); ?>
                            </div>
                            <div class="volunteer-info">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($volunteer['Phone']); ?>
                            </div>

                            <div class="btn-group-sm d-flex gap-2 mt-3">
                                <a href="VolunteerController.php?action=view&id=<?php echo $volunteer['VolunteerID']; ?>" 
                                   class="btn btn-sm btn-primary flex-grow-1">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> No volunteers found matching your search.
            </div>
        <?php endif; ?>

        <a href="VolunteerController.php?action=list" class="btn btn-secondary mt-4">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
