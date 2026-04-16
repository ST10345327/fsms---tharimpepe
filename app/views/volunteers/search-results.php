<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .volunteer-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .volunteer-card h5 { color: #333; margin-bottom: 10px; }
        .volunteer-info { font-size: 14px; color: #666; margin: 5px 0; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

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

    <!-- Footer -->
    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
