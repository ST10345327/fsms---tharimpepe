<?php
/**
 * Module: Shared Navigation Component
 * Purpose: Enterprise-style sidebar and top bar used across authenticated pages
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
$currentUser = getCurrentUser();
$username = $currentUser['username'] ?? 'Guest';
$role = $currentUser['role'] ?? 'volunteer';
$displayName = strtolower($username) === 'admin' ? 'Admin User' : $username;
$displayRole = strtolower($role) === 'admin' ? 'Administrator' : ucfirst($role);
$initials = strtolower($username) === 'admin' ? 'AD' : strtoupper(substr($username, 0, 1));
$currentAction = basename($_SERVER['PHP_SELF'] ?? '');

$navItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-house', 'href' => '../controllers/DashboardController.php?action=overview', 'match' => 'DashboardController.php'],
    ['label' => 'Beneficiaries', 'icon' => 'fa-users', 'href' => '../controllers/BeneficiaryController.php?action=list', 'match' => 'BeneficiaryController.php'],
    ['label' => 'Attendance', 'icon' => 'fa-clipboard-check', 'href' => '../controllers/AttendanceController.php?action=list', 'match' => 'AttendanceController.php'],
    ['label' => 'Food Stock', 'icon' => 'fa-boxes-stacked', 'href' => '../controllers/FoodStockController.php?action=list', 'match' => 'FoodStockController.php'],
    ['label' => 'Volunteers', 'icon' => 'fa-user-check', 'href' => '../controllers/VolunteerScheduleController.php?action=list', 'match' => 'VolunteerScheduleController.php'],
    ['label' => 'Donations', 'icon' => 'fa-hand-holding-dollar', 'href' => '../controllers/DonationController.php?action=list', 'match' => 'DonationController.php'],
    ['label' => 'Reports', 'icon' => 'fa-file-lines', 'href' => '../controllers/ReportsController.php?action=dashboard', 'match' => 'ReportsController.php'],
    ['label' => 'Users', 'icon' => 'fa-shield-halved', 'href' => '../controllers/UserController.php?action=list', 'match' => 'UserController.php'],
];
?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/fsms-ui.css">

<aside class="fsms-sidebar" aria-label="Primary navigation">
    <a class="fsms-brand" href="../controllers/DashboardController.php?action=overview">
           <img class="fsms-brand-logo" src="/assets/images/tharimpepe-logo.svg"
               srcset="/assets/images/generate_raster.php?name=tharimpepe-logo&w=172&h=48&dpr=1 1x, /assets/images/generate_raster.php?name=tharimpepe-logo&w=344&h=96&dpr=2 2x"
               sizes="(max-width:640px) 58px, (max-width:992px) 120px, 172px"
               alt="Tharimpepe" loading="lazy" width="172" height="48">
        <span class="fsms-brand-title">Feeding Scheme Management</span>
    </a>

    <nav class="fsms-nav">
        <?php foreach ($navItems as $item): ?>
            <a class="fsms-nav-link <?php echo $currentAction === $item['match'] ? 'active' : ''; ?>"
               href="<?php echo htmlspecialchars($item['href']); ?>">
                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                <span class="fsms-nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>

        <div class="fsms-nav-bottom">
            <a class="fsms-nav-link" href="../controllers/AuthController.php?action=logout">
                <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span class="fsms-nav-text">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Offcanvas nav for small screens -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="fsmsOffcanvas" aria-labelledby="fsmsOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="fsmsOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="fsms-nav offcanvas-nav">
            <?php foreach ($navItems as $item): ?>
                <a class="fsms-nav-link <?php echo $currentAction === $item['match'] ? 'active' : ''; ?>"
                   href="<?php echo htmlspecialchars($item['href']); ?>">
                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                    <span class="fsms-nav-text"><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>

            <div class="fsms-nav-bottom">
                <a class="fsms-nav-link" href="../controllers/AuthController.php?action=logout">
                    <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span class="fsms-nav-text">Logout</span>
                </a>
            </div>
        </nav>
    </div>
</div>

<header class="fsms-topbar">
    <button class="fsms-hamburger" type="button" data-bs-toggle="offcanvas" data-bs-target="#fsmsOffcanvas" aria-controls="fsmsOffcanvas" aria-label="Open menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    <div>
        <h1 class="fsms-page-title"><?php echo htmlspecialchars($pageTitle ?? 'Admin Portal'); ?></h1>
        <div class="fsms-page-subtitle">Tharimpepe Feeding Scheme</div>
    </div>
    <div class="fsms-top-actions">
        <a class="fsms-icon-button" href="../controllers/MessageController.php?action=inbox" aria-label="View notifications and messages">
            <i class="fas fa-bell" aria-hidden="true"></i>
            <span class="fsms-alert-dot" aria-hidden="true"></span>
        </a>
        <div class="fsms-user-chip">
            <span class="fsms-avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></span>
            <span class="fsms-user-meta">
                <span class="fsms-user-name"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="fsms-user-role"><?php echo htmlspecialchars($displayRole); ?></span>
            </span>
        </div>
    </div>
</header>
